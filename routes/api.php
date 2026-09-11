<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameTicketController;
use App\Http\Middleware\AuthenticateGameServer;
use Illuminate\Support\Facades\Route;

Route::post('/status', [AuthController::class, 'status']);

// Players call this route with a normal Oculus access token. Sanctum verifies
// that token, the controller checks its game-ticket:create ability, and the
// throttle prevents one client from creating more than ten tickets per minute.
Route::post('/game-tickets', [GameTicketController::class, 'store'])->middleware(['auth:sanctum', 'throttle:10,1']);

// Only game servers call this internal route. AuthenticateGameServer checks the
// separate shared server credential before the controller sees the player ticket.
Route::post('/internal/game-tickets/consume', [GameTicketController::class, 'consume'])->middleware(AuthenticateGameServer::class);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/create_character', [AuthController::class, 'createCharacter']);
        Route::get('/session', [AuthController::class, 'show']);
        Route::put('/session', [AuthController::class, 'update']);
        Route::delete('/session', [AuthController::class, 'destroy']);
        Route::delete('/sessions', [AuthController::class, 'destroyAll']);
    });
});