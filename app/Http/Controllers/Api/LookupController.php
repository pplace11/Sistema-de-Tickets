<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inbox;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;

class LookupController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'inboxes' => Inbox::query()->orderBy('name', 'asc')->get(),
            'ticketTypes' => TicketType::query()->where('is_active', '=', true)->orderBy('name', 'asc')->get(),
            'ticketStatuses' => TicketStatus::query()->where('is_active', '=', true)->orderBy('id', 'asc')->get(),
        ]);
    }
}
