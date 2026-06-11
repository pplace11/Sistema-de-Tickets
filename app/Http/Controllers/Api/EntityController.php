<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EntityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Entity::query()->with('contacts');

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
        return response()->json($entity->load('contacts'));
    }

    public function update(Request $request, Entity $entity): JsonResponse
    {
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
}
