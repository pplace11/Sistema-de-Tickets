<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactFunction;
use Illuminate\Http\JsonResponse;

class ContactFunctionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            ContactFunction::query()->orderBy('name', 'asc')->get()
        );
    }
}
