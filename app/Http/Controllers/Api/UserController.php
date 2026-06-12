<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureOperator($request->user());

        $query = User::query()->with(['entity', 'inboxes']);

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
            'entity_id' => ['required', 'exists:entities,id'],
            'inbox_ids' => ['nullable', 'array'],
            'inbox_ids.*' => ['integer', 'exists:inboxes,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'operator',
            'entity_id' => $data['entity_id'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $user->inboxes()->sync($data['inbox_ids'] ?? []);

        return response()->json($user->fresh()->load(['entity', 'inboxes']), 201);
    }

    private function ensureOperator(?User $user): void
    {
        abort_if(! $user, 401, 'Utilizador nao autenticado.');
        abort_if(! $user->isOperator(), 403, 'Apenas operadores podem gerir utilizadores.');
    }
}
