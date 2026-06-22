<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Utilizador nao autenticado.');

        $query = Entity::query()->with('contacts');

        if (! $user->isOperator()) {
            $allowedEntityIds = $user->accessibleEntityIds();

            abort_if($allowedEntityIds->isEmpty(), 403, 'Cliente sem entidade associada.');
            $query->whereIn('id', $allowedEntityIds->all());
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nif', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('name')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureOperator($request->user());

        $data = $request->validate([
            'nif' => ['required', 'string', 'max:32', 'unique:entities,nif'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $entity = Entity::create($data);

        return response()->json($entity, 201);
    }

    public function show(Entity $entity): JsonResponse
    {
        $this->ensureCanAccessEntity($entity, request()->user());

        return response()->json($entity->load('contacts'));
    }

    public function update(Request $request, Entity $entity): JsonResponse
    {
        $this->ensureOperator($request->user());

        $data = $request->validate([
            'nif' => ['required', 'string', 'max:32', 'unique:entities,nif,'.$entity->id],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $entity->update($data);

        return response()->json($entity);
    }

    private function ensureOperator(?User $user): void
    {
        abort_if(! $user, 401, 'Utilizador nao autenticado.');
        abort_if(! $user->isOperator(), 403, 'Apenas operadores podem executar esta operacao.');
    }

    private function ensureCanAccessEntity(Entity $entity, ?User $user): void
    {
        abort_if(! $user, 401, 'Utilizador nao autenticado.');

        if ($user->isOperator()) {
            return;
        }

        $allowedEntityIds = $user->accessibleEntityIds();

        abort_if($allowedEntityIds->isEmpty(), 403, 'Cliente sem entidade associada.');
        abort_if(! $allowedEntityIds->contains((int) $entity->id), 403, 'Sem permissao para esta entidade.');
    }
}
