<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketReplyNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketReplyController extends Controller
{
    public function store(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Utilizador nao autenticado.');
        $this->ensureCanAccessTicket($ticket, $user);

        $data = $request->validate([
            'message' => ['required', 'string'],
            'author_contact_id' => ['nullable', 'exists:contacts,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        if (! empty($data['author_contact_id'])) {
            $this->ensureContactBelongsToEntity((int) $data['author_contact_id'], (int) $ticket->entity_id);
        }

        $authorType = $user->isOperator() ? 'operator' : 'client';

        $reply = DB::transaction(function () use ($request, $ticket, $data, $authorType) {
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
            'author_type' => $authorType,
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
                    'author_type' => $authorType,
                ],
            ]);

            return $reply;
        });

        $this->notifyReply($ticket, $reply);

        return response()->json($reply->load(['authorUser', 'authorContact', 'attachments']), 201);
    }

    private function notifyReply(Ticket $ticket, TicketReply $reply): void
    {
        $ticket->loadMissing(['cc', 'creatorContact', 'creatorUser', 'assignedOperator', 'entity.contacts']);

        $emails = collect($ticket->cc->pluck('email'));

        if ($ticket->creatorContact?->email) {
            $emails->push($ticket->creatorContact->email);
        }

        if ($ticket->creatorUser?->email) {
            $emails->push($ticket->creatorUser->email);
        }

        if ($ticket->assignedOperator?->email) {
            $emails->push($ticket->assignedOperator->email);
        }

        if ($ticket->entity?->email) {
            $emails->push($ticket->entity->email);
        }

        $entityContactEmails = $ticket->entity?->contacts?->pluck('email') ?? collect();
        $emails = $emails->merge($entityContactEmails);

        $emails = $emails->filter()->unique()->values();

        if ($emails->isNotEmpty()) {
            Notification::route('mail', $emails->all())
                ->notify(new TicketReplyNotification($ticket, $reply));
        }
    }

    private function ensureCanAccessTicket(Ticket $ticket, User $user): void
    {
        if ($user->isOperator()) {
            $hasInboxAccess = $user->inboxes()->where('inboxes.id', $ticket->inbox_id)->exists();
            abort_if(! $hasInboxAccess, 403, 'Operador sem permissao para esta inbox.');

            return;
        }

        $allowedEntityIds = $user->accessibleEntityIds();

        abort_if($allowedEntityIds->isEmpty(), 403, 'Cliente sem entidade associada.');
        abort_if(! $allowedEntityIds->contains((int) $ticket->entity_id), 403, 'Sem permissao para este ticket.');
    }

    private function ensureContactBelongsToEntity(int $contactId, int $entityId): void
    {
        $isValid = Contact::query()
            ->whereKey($contactId)
            ->whereHas('entities', fn ($q) => $q->where('entities.id', $entityId))
            ->exists();

        abort_if(! $isValid, 422, 'O contacto informado nao pertence a entidade do ticket.');
    }
}
