<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureOperator($request->user());

        $query = User::query()->with(['entity', 'entities', 'inboxes']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', (string) $request->input('role'));
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureOperator($request->user());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['operator', 'client'])],
            'entity_id' => ['nullable', 'exists:entities,id'],
            'entity_ids' => ['nullable', 'array'],
            'entity_ids.*' => ['integer', 'exists:entities,id'],
            'inbox_ids' => ['nullable', 'array'],
            'inbox_ids.*' => ['integer', 'exists:inboxes,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $entityIds = $this->normalizeEntityIds($data['entity_ids'] ?? [], $data['entity_id'] ?? null);

        if (($data['role'] ?? 'operator') === 'client') {
            abort_if($entityIds->isEmpty(), 422, 'Clientes devem ter pelo menos uma entidade.');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'entity_id' => $entityIds->first(),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $user->entities()->sync($entityIds->all());

        if ($user->isOperator()) {
            $user->inboxes()->sync($data['inbox_ids'] ?? []);
        } else {
            $user->inboxes()->sync([]);
        }

        return response()->json($user->fresh()->load(['entity', 'entities', 'inboxes']), 201);
    }

    public function syncEntities(Request $request, User $user): JsonResponse
    {
        $this->ensureOperator($request->user());

        $data = $request->validate([
            'entity_ids' => ['required', 'array', 'min:1'],
            'entity_ids.*' => ['integer', 'exists:entities,id'],
        ]);

        $entityIds = collect($data['entity_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $user->entities()->sync($entityIds->all());
        $user->entity_id = $entityIds->first();
        $user->save();

        return response()->json($user->fresh()->load(['entity', 'entities', 'inboxes']));
    }

    /**
     * @param array<int, int|string> $entityIds
     */
    private function normalizeEntityIds(array $entityIds, int|string|null $legacyEntityId): Collection
    {
        $normalized = collect($entityIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        if ($legacyEntityId !== null) {
            $normalized->push((int) $legacyEntityId);
        }

        return $normalized->unique()->values();
    }

    private function ensureOperator(?User $user): void
    {
        abort_if(! $user, 401, 'Utilizador nao autenticado.');
        abort_if(! $user->isOperator(), 403, 'Apenas operadores podem gerir utilizadores.');
    }
}
