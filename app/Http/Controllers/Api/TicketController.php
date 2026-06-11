<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query()->with([
            'inbox',
            'type',
            'status',
            'entity',
            'assignedOperator',
            'creatorContact',
            'cc',
        ]);

        if ($request->filled('inbox_id')) {
            $query->where('inbox_id', (int) $request->input('inbox_id'));
        }

        if ($request->filled('ticket_status_id')) {
            $query->where('ticket_status_id', (int) $request->input('ticket_status_id'));
        }

        if ($request->filled('ticket_type_id')) {
            $query->where('ticket_type_id', (int) $request->input('ticket_type_id'));
        }

        if ($request->filled('assigned_operator_id')) {
            $query->where('assigned_operator_id', (int) $request->input('assigned_operator_id'));
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', (int) $request->input('entity_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('entity', fn ($entityQuery) => $entityQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('creatorContact', fn ($contactQuery) => $contactQuery->where('email', 'like', "%{$search}%"));
            });
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show(Ticket $ticket): JsonResponse
    {
        return response()->json(
            $ticket->load([
                'inbox',
                'type',
                'status',
                'entity',
                'assignedOperator',
                'creatorContact',
                'creatorUser',
                'cc',
                'attachments',
                'replies.authorUser',
                'replies.authorContact',
                'replies.attachments',
                'activityLogs.user',
            ])
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'inbox_id' => ['required', 'exists:inboxes,id'],
            'ticket_type_id' => ['required', 'exists:ticket_types,id'],
            'ticket_status_id' => ['required', 'exists:ticket_statuses,id'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
            'entity_id' => ['required', 'exists:entities,id'],
            'creator_contact_id' => ['nullable', 'exists:contacts,id'],
            'message' => ['required', 'string'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['email'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $ticket = DB::transaction(function () use ($request, $data) {
            $ticket = Ticket::create([
                'ticket_number' => $this->generateTicketNumber(),
                'subject' => $data['subject'],
                'inbox_id' => $data['inbox_id'],
                'ticket_type_id' => $data['ticket_type_id'],
                'ticket_status_id' => $data['ticket_status_id'],
                'assigned_operator_id' => $data['assigned_operator_id'] ?? null,
                'entity_id' => $data['entity_id'],
                'creator_contact_id' => $data['creator_contact_id'] ?? null,
                'creator_user_id' => $request->user()?->id,
                'message' => $data['message'],
            ]);

            foreach ($data['cc'] ?? [] as $email) {
                $ticket->cc()->create(['email' => $email]);
            }

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store('tickets/'.$ticket->id, 'public');
                TicketAttachment::create([
                    'ticket_id' => $ticket->id,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }

            ActivityLog::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()?->id,
                'action' => 'ticket_created',
                'description' => 'Ticket criado',
                'meta' => [
                    'ticket_number' => $ticket->ticket_number,
                ],
            ]);

            return $ticket;
        });

        $ticket->load(['cc', 'creatorContact', 'creatorUser']);
        $this->notifyTicketCreated($ticket);

        return response()->json($ticket, 201);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'ticket_status_id' => ['sometimes', 'required', 'exists:ticket_statuses,id'],
            'ticket_type_id' => ['sometimes', 'required', 'exists:ticket_types,id'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
            'inbox_id' => ['sometimes', 'required', 'exists:inboxes,id'],
        ]);

        $ticket->update($data);

        ActivityLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()?->id,
            'action' => 'ticket_updated',
            'description' => 'Ticket atualizado',
            'meta' => $data,
        ]);

        return response()->json($ticket->fresh()->load(['inbox', 'type', 'status', 'entity', 'assignedOperator']));
    }

    private function generateTicketNumber(): string
    {
        $last = Ticket::query()->latest('id')->value('id') ?? 0;

        return 'TC-'.str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }

    private function notifyTicketCreated(Ticket $ticket): void
    {
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

        $emails = $emails->filter()->unique()->values();

        if ($emails->isNotEmpty()) {
            Notification::route('mail', $emails->all())
                ->notify(new TicketCreatedNotification($ticket));
        }
    }
}
