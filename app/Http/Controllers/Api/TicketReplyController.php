<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Notifications\TicketReplyNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketReplyController extends Controller
{
    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string'],
            'author_type' => ['required', 'in:operator,client'],
            'author_contact_id' => ['nullable', 'exists:contacts,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $reply = DB::transaction(function () use ($request, $ticket, $data) {
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'author_type' => $data['author_type'],
                'author_user_id' => $request->user()?->id,
                'author_contact_id' => $data['author_contact_id'] ?? null,
                'message' => $data['message'],
            ]);

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store('tickets/'.$ticket->id.'/replies/'.$reply->id, 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'ticket_reply_id' => $reply->id,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }

            ActivityLog::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()?->id,
                'action' => 'ticket_reply_created',
                'description' => 'Nova resposta no ticket',
                'meta' => [
                    'reply_id' => $reply->id,
                    'author_type' => $data['author_type'],
                ],
            ]);

            return $reply;
        });

        $this->notifyReply($ticket, $reply);

        return response()->json($reply->load(['authorUser', 'authorContact', 'attachments']), 201);
    }

    private function notifyReply(Ticket $ticket, TicketReply $reply): void
    {
        $ticket->loadMissing(['cc', 'creatorContact', 'creatorUser']);

        $emails = collect($ticket->cc->pluck('email'));

        if ($ticket->creatorContact?->email) {
            $emails->push($ticket->creatorContact->email);
        }

        if ($ticket->creatorUser?->email) {
            $emails->push($ticket->creatorUser->email);
        }

        $emails = $emails->filter()->unique()->values();

        if ($emails->isNotEmpty()) {
            Notification::route('mail', $emails->all())
                ->notify(new TicketReplyNotification($ticket, $reply));
        }
    }
}
