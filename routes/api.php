<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameTicketController;
use App\Http\Middleware\AuthenticateGameServer;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('/status', [AuthController::class, 'status']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/session', [AuthController::class, 'show']);
        Route::put('/session', [AuthController::class, 'update']);
        Route::delete('/session', [AuthController::class, 'destroy']);
        Route::delete('/sessions', [AuthController::class, 'destroyAll']);
    });

});

Route::post('/game-tickets', [GameTicketController::class, 'store'])->middleware(['auth:sanctum', 'throttle:10,1']);

Route::post('/internal/game-tickets/consume', [GameTicketController::class, 'consume'])->middleware(AuthenticateGameServer::class);
