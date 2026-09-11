<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateGameServer
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // This credential authenticates the game server itself, not the player.
        // Only a trusted game server should be allowed to consume player tickets.
        // The Phaser client knows the player's ticket but never receives this
        // shared server credential.
        $expectedToken = config('oculus.game_server_token');
        $providedToken = $request->bearerToken();

        // Treat missing server configuration as an authentication failure rather
        // than accidentally accepting an empty credential.
        if (! is_string($expectedToken) || $expectedToken === '') {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid game server credentials.');
        }

        if (! is_string($providedToken) || $providedToken === '') {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid game server credentials.');
        }

        // Constant-time comparison avoids exposing partial token matches through
        // request timing. The same generic error is returned for every failure.
        if (! hash_equals($expectedToken, $providedToken)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid game server credentials.');
        }

        // The request reaches GameTicketController::consume only after the game
        // server has authenticated successfully.
        return $next($request);
    }
}
