<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CharacterController;
use App\Http\Controllers\Api\GameTicketController;
use App\Http\Controllers\Api\StatusController;
use App\Http\Middleware\AuthenticateGameServer;
use Illuminate\Support\Facades\Route;

Route::get('/status', StatusController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Players call this route with a normal Sanctum access token. Sanctum verifies
// that token, the controller checks its game-ticket:create ability, and the
Route::post('/create-game-ticket', [GameTicketController::class, 'store'])->middleware(['auth:sanctum', 'throttle:10,1']);

Route::post('create-character', [CharacterController::class, 'store'])->middleware('auth:sanctum');

// Only game servers call this internal route. AuthenticateGameServer checks the separate shared server credential before the controller sees the player ticket.
Route::post('/internal/game-tickets/consume', [GameTicketController::class, 'consume'])->middleware(AuthenticateGameServer::class);

Route::prefix('auth')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/session', [AuthController::class, 'show']);
        Route::put('/session', [AuthController::class, 'update']);
        Route::delete('/session', [AuthController::class, 'destroy']);
        Route::delete('/sessions', [AuthController::class, 'destroyAll']);
    });
});