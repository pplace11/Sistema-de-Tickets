<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ContactFunctionController;
use App\Http\Controllers\Api\EntityController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketReplyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
});

Route::get('/lookups', [LookupController::class, 'index']);
Route::get('/contact-functions', [ContactFunctionController::class, 'index']);
Route::apiResource('entities', EntityController::class)->only(['index', 'store', 'show', 'update']);
Route::apiResource('contacts', ContactController::class)->only(['index', 'store', 'show', 'update']);
Route::apiResource('tickets', TicketController::class)->only(['index', 'store', 'show', 'update']);
Route::post('/tickets/{ticket}/replies', [TicketReplyController::class, 'store']);
