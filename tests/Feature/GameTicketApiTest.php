<?php

namespace Tests\Feature;

use App\Models\GameTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTicketApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'oculus.game_server_token' => 'test-game-server-token',
            'oculus.game_ticket_expiration_seconds' => 60,
        ]);
    }

    public function test_a_player_can_create_and_a_game_server_can_consume_a_ticket(): void
    {
        $user = User::factory()->create();
        $accessToken = $user->createToken('test-client', ['game-ticket:create'])->plainTextToken;

        $created = $this->withToken($accessToken)
            ->postJson('/api/game-tickets')
            ->assertCreated()
            ->assertJsonStructure(['data' => ['ticket', 'expires_at']]);

        $plainTextTicket = $created->json('data.ticket');

        $consumed = $this->withToken('test-game-server-token')
            ->postJson('/api/internal/game-tickets/consume', ['ticket' => $plainTextTicket])
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', $user->name);

        $this->assertNotNull($consumed);
        $this->assertDatabaseMissing('game_tickets', ['token_hash' => $plainTextTicket]);
        $this->assertNotNull(GameTicket::first()->consumed_at);

        $this->withToken('test-game-server-token')
            ->postJson('/api/internal/game-tickets/consume', ['ticket' => $plainTextTicket])
            ->assertUnauthorized();
    }

    public function test_a_token_without_the_game_ticket_ability_is_forbidden(): void
    {
        $user = User::factory()->create();
        $accessToken = $user->createToken('limited-client', ['account:read'])->plainTextToken;

        $this->withToken($accessToken)
            ->postJson('/api/game-tickets')
            ->assertForbidden();
    }

    public function test_the_consume_endpoint_requires_game_server_credentials(): void
    {
        $this->postJson('/api/internal/game-tickets/consume', ['ticket' => 'invalid'])
            ->assertUnauthorized();
    }
}
