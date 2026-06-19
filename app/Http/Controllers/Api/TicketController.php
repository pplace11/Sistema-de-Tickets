<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Utilizador nao autenticado.');

        $query = Ticket::query()->with([
            'inbox',
            'type',
            'status',
            'entity',
            'assignedOperator',
            'creatorContact',
            'cc',
        ]);

        if ($user->isOperator()) {
            $allowedInboxIds = $user->inboxes()->pluck('inboxes.id');
            $query->whereIn('inbox_id', $allowedInboxIds);
        } else {
            abort_if(! $user->entity_id, 403, 'Cliente sem entidade associada.');
            $query->where('entity_id', (int) $user->entity_id);
        }

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
            $requestedEntityId = (int) $request->input('entity_id');

            if (! $user->isOperator() && $requestedEntityId !== (int) $user->entity_id) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('entity_id', $requestedEntityId);
            }
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
        $user = request()->user();

        abort_if(! $user, 401, 'Utilizador nao autenticado.');
        $this->ensureCanAccessTicket($ticket, $user);

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
        $user = $request->user();

        abort_if(! $user, 401, 'Utilizador nao autenticado.');

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

        if ($user->isOperator()) {
            $this->ensureInboxAccess($user, (int) $data['inbox_id']);
        } else {
            abort_if(! $user->entity_id, 403, 'Cliente sem entidade associada.');
            abort_if(
                (int) $data['entity_id'] !== (int) $user->entity_id,
                403,
                'Clientes so podem criar tickets na sua entidade.'
            );
            abort_if(
                ! empty($data['assigned_operator_id']),
                403,
                'Clientes nao podem atribuir operadores diretamente.'
            );
        }

        $this->ensureContactBelongsToEntity($data['creator_contact_id'] ?? null, (int) $data['entity_id']);

        if (! empty($data['assigned_operator_id'])) {
            $this->ensureAssignableOperator((int) $data['assigned_operator_id'], (int) $data['inbox_id']);
        }

        $ticket = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
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

                break;
            } catch (QueryException $exception) {
                $duplicateEntry = in_array((string) ($exception->errorInfo[0] ?? ''), ['23000', '23505'], true);

                if (! $duplicateEntry || $attempt === 2) {
                    throw $exception;
                }
            }
        }

        abort_if(! $ticket, 500, 'Nao foi possivel criar o ticket.');

        $ticket->load(['cc', 'creatorContact', 'creatorUser']);
        $this->notifyTicketCreated($ticket);

        return response()->json($ticket, 201);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Utilizador nao autenticado.');
        $this->ensureOperator($user);
        $this->ensureCanAccessTicket($ticket, $user);

        $data = $request->validate([
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'ticket_status_id' => ['sometimes', 'required', 'exists:ticket_statuses,id'],
            'ticket_type_id' => ['sometimes', 'required', 'exists:ticket_types,id'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
            'inbox_id' => ['sometimes', 'required', 'exists:inboxes,id'],
        ]);

        $previousAssignedOperatorId = $ticket->assigned_operator_id;

        $targetInboxId = isset($data['inbox_id']) ? (int) $data['inbox_id'] : (int) $ticket->inbox_id;
        $this->ensureInboxAccess($user, $targetInboxId);

        if (! empty($data['assigned_operator_id'])) {
            $this->ensureAssignableOperator((int) $data['assigned_operator_id'], $targetInboxId);
        }

        $ticket->update($data);

        if (array_key_exists('assigned_operator_id', $data) && $this->hasAssignmentChanged($previousAssignedOperatorId, $ticket->assigned_operator_id)) {
            $ticket->loadMissing(['assignedOperator']);
            $this->notifyTicketAssigned($ticket);
        }

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

    private function hasAssignmentChanged(?int $previousOperatorId, ?int $currentOperatorId): bool
    {
        return (int) ($previousOperatorId ?? 0) !== (int) ($currentOperatorId ?? 0);
    }

    private function notifyTicketAssigned(Ticket $ticket): void
    {
        if (! $ticket->assignedOperator?->email) {
            return;
        }

        Notification::route('mail', $ticket->assignedOperator->email)
            ->notify(new TicketAssignedNotification($ticket));
    }

    private function ensureOperator(User $user): void
    {
        abort_if(! $user->isOperator(), 403, 'Apenas operadores podem executar esta operacao.');
    }

    private function ensureCanAccessTicket(Ticket $ticket, User $user): void
    {
        if ($user->isOperator()) {
            $this->ensureInboxAccess($user, (int) $ticket->inbox_id);

            return;
        }

        abort_if(! $user->entity_id, 403, 'Cliente sem entidade associada.');
        abort_if((int) $ticket->entity_id !== (int) $user->entity_id, 403, 'Sem permissao para este ticket.');
    }

    private function ensureInboxAccess(User $user, int $inboxId): void
    {
        $allowed = $user->inboxes()->where('inboxes.id', $inboxId)->exists();
        abort_if(! $allowed, 403, 'Operador sem permissao para esta inbox.');
    }

    private function ensureContactBelongsToEntity(?int $contactId, int $entityId): void
    {
        if (! $contactId) {
            return;
        }

        $isValid = Contact::query()
            ->whereKey($contactId)
            ->whereHas('entities', fn ($q) => $q->where('entities.id', $entityId))
            ->exists();

        abort_if(! $isValid, 422, 'O contacto criador nao pertence a entidade selecionada.');
    }

    private function ensureAssignableOperator(int $operatorId, int $inboxId): void
    {
        $operator = User::query()
            ->whereKey($operatorId)
            ->where('role', 'operator')
            ->first();

        abort_if(! $operator, 422, 'Operador associado invalido.');

        $hasInboxAccess = $operator->inboxes()->where('inboxes.id', $inboxId)->exists();
        abort_if(! $hasInboxAccess, 422, 'O operador associado nao tem acesso a inbox selecionada.');
    }
}
