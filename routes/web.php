<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth');

Route::view('/{any?}', 'app')->where('any', '^(?!api).*$');
