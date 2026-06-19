<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbox;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $inboxes = $user?->isOperator()
            ? $user->inboxes()->where('inboxes.is_active', '=', true)->orderBy('inboxes.name', 'asc')->get()
            : Inbox::query()->where('is_active', '=', true)->orderBy('name', 'asc')->get();

        $operators = collect();

        if ($user?->isOperator()) {
            $allowedInboxIds = $user->inboxes()->pluck('inboxes.id');

            $operators = User::query()
                ->where('role', 'operator')
                ->where('is_active', true)
                ->whereHas('inboxes', fn ($q) => $q->whereIn('inboxes.id', $allowedInboxIds))
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'email']);
        }

        return response()->json([
            'inboxes' => $inboxes,
            'ticketTypes' => TicketType::query()->where('is_active', '=', true)->orderBy('name', 'asc')->get(),
            'ticketStatuses' => TicketStatus::query()->where('is_active', '=', true)->orderBy('id', 'asc')->get(),
            'operators' => $operators,
        ]);
    }
}
