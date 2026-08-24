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
        $expectedToken = config('oculus.game_server_token');
        $providedToken = $request->bearerToken();

        if (! is_string($expectedToken)
            || $expectedToken === ''
            || ! is_string($providedToken)
            || ! hash_equals($expectedToken, $providedToken)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid game server credentials.');
        }

        return $next($request);
    }
}
