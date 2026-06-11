<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query()->with(['contactFunction', 'entities']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('entity_id')) {
            $entityId = (int) $request->input('entity_id');
            $query->whereHas('entities', fn ($q) => $q->where('entities.id', $entityId));
        }

        return response()->json($query->orderBy('name')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
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
        return response()->json($contact->load(['contactFunction', 'entities']));
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
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
}
