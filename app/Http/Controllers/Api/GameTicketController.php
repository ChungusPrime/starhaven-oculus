<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameTicket;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameTicketController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->tokenCan('game-ticket:create'), 403);

        $secret = Str::random(64);
        $expiresAt = now()->addSeconds(config('oculus.game_ticket_expiration_seconds'));
        $ticket = GameTicket::create([
            'user_id' => $request->user()->id,
            'token_hash' => hash('sha256', $secret),
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'data' => [
                'ticket' => $ticket->id.'.'.$secret,
                'expires_at' => $expiresAt->toISOString(),
            ],
        ], 201);
    }

    /**
     * @throws AuthenticationException
     */
    public function consume(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ticket' => ['required', 'string', 'max:128'],
        ]);

        [$ticketId, $secret] = array_pad(explode('.', $validated['ticket'], 2), 2, null);

        if (! is_string($ticketId) || ! Str::isUuid($ticketId) || ! is_string($secret)) {
            throw new AuthenticationException('Invalid or expired game ticket.');
        }

        $ticket = DB::transaction(function () use ($ticketId, $secret): GameTicket {
            $ticket = GameTicket::query()->lockForUpdate()->find($ticketId);

            if (! $ticket
                || $ticket->consumed_at !== null
                || $ticket->expires_at->isPast()
                || ! hash_equals($ticket->token_hash, hash('sha256', $secret))) {
                throw new AuthenticationException('Invalid or expired game ticket.');
            }

            $ticket->update(['consumed_at' => now()]);

            return $ticket;
        });

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $ticket->user->id,
                    'name' => $ticket->user->name,
                ],
            ],
        ]);
    }
}
