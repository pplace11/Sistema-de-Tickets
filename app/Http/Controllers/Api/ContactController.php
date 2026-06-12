<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Utilizador nao autenticado.');

        $query = Contact::query()->with(['contactFunction', 'entities']);

        if (! $user->isOperator()) {
            abort_if(! $user->entity_id, 403, 'Cliente sem entidade associada.');
            $query->whereHas('entities', fn ($q) => $q->where('entities.id', (int) $user->entity_id));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('entity_id')) {
            $entityId = (int) $request->input('entity_id');

            if (! $user->isOperator() && $entityId !== (int) $user->entity_id) {
                $query->whereRaw('1 = 0');
            }

            $query->whereHas('entities', fn ($q) => $q->where('entities.id', $entityId));
        }

        return response()->json($query->orderBy('name')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureOperator($request->user());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_function_id' => ['nullable', 'exists:contact_functions,id'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'internal_notes' => ['nullable', 'string'],
            'entity_ids' => ['nullable', 'array'],
            'entity_ids.*' => ['integer', 'exists:entities,id'],
        ]);

        $contact = Contact::create($data);
        $contact->entities()->sync($data['entity_ids'] ?? []);

        return response()->json($contact->load(['contactFunction', 'entities']), 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $this->ensureCanAccessContact($contact, request()->user());

        return response()->json($contact->load(['contactFunction', 'entities']));
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $this->ensureOperator($request->user());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_function_id' => ['nullable', 'exists:contact_functions,id'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'internal_notes' => ['nullable', 'string'],
            'entity_ids' => ['nullable', 'array'],
            'entity_ids.*' => ['integer', 'exists:entities,id'],
        ]);

        $contact->update($data);
        $contact->entities()->sync($data['entity_ids'] ?? []);

        return response()->json($contact->load(['contactFunction', 'entities']));
    }

    private function ensureOperator(?User $user): void
    {
        abort_if(! $user, 401, 'Utilizador nao autenticado.');
        abort_if(! $user->isOperator(), 403, 'Apenas operadores podem executar esta operacao.');
    }

    private function ensureCanAccessContact(Contact $contact, ?User $user): void
    {
        abort_if(! $user, 401, 'Utilizador nao autenticado.');

        if ($user->isOperator()) {
            return;
        }

        abort_if(! $user->entity_id, 403, 'Cliente sem entidade associada.');

        $allowed = $contact->entities()->where('entities.id', (int) $user->entity_id)->exists();
        abort_if(! $allowed, 403, 'Sem permissao para este contacto.');
    }
}
