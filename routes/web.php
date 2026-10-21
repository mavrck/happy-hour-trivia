<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\HostController;
use App\Http\Middleware\EnsureHost;
use Illuminate\Support\Facades\Route;

Route::view('/', 'app', ['view' => 'player']);
Route::view('/board', 'app', ['view' => 'board']);
Route::view('/host', 'app', ['view' => 'host']);

Route::post('/api/join', [GameController::class, 'join']);
Route::get('/api/state', [GameController::class, 'state']);
Route::post('/api/answer', [GameController::class, 'answer']);

Route::post('/host/login', [HostController::class, 'login']);
Route::middleware(EnsureHost::class)->group(function () {
    Route::get('/host/state', [HostController::class, 'state']);
    Route::post('/host/action', [HostController::class, 'action']);
});
