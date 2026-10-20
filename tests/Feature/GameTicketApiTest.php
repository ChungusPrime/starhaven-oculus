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
        $character = $user->characters()->create([
            'name' => 'UNIT#ABC-123-XYZ',
            'status' => 'active',
            'area' => 'Starhaven Pioneer Base',
        ]);
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
            ->assertJsonPath('data.user.username', $user->username)
            ->assertJsonPath('data.character.id', $character->id)
            ->assertJsonPath('data.character.name', $character->name)
            ->assertJsonPath('data.character.status', 'active');

        $this->assertNotNull($consumed);

        // The database stores a hash rather than the plaintext credential that
        // travelled through the client and game server.
        $storedTicket = GameTicket::query()->firstOrFail();
        $this->assertNotSame($plainTextTicket, $storedTicket->token_hash);
        $this->assertNotNull($storedTicket->consumed_at);

        $this->withToken('test-game-server-token')
            ->postJson('/api/internal/game-tickets/consume', ['ticket' => $plainTextTicket])
            ->assertUnauthorized();
    }

    public function test_an_expired_ticket_is_rejected_even_before_cleanup_deletes_it(): void
    {
        $user = User::factory()->create();
        $user->characters()->create([
            'name' => 'UNIT#ABC-123-XYZ',
            'status' => 'active',
        ]);
        $accessToken = $user->createToken('test-client', ['game-ticket:create'])->plainTextToken;

        $created = $this->withToken($accessToken)
            ->postJson('/api/game-tickets')
            ->assertCreated();

        $plainTextTicket = $created->json('data.ticket');

        // Move beyond the configured 60-second lifetime. The row still exists,
        // proving validity does not depend on waiting for the hourly prune job.
        $this->travel(61)->seconds();
        $this->assertDatabaseCount('game_tickets', 1);

        $this->withToken('test-game-server-token')
            ->postJson('/api/internal/game-tickets/consume', ['ticket' => $plainTextTicket])
            ->assertUnauthorized();

        $this->assertNull(GameTicket::query()->firstOrFail()->consumed_at);
    }

    public function test_a_player_without_an_active_character_cannot_create_a_ticket(): void
    {
        $user = User::factory()->create();
        $accessToken = $user->createToken('test-client', ['game-ticket:create'])->plainTextToken;

        $this->withToken($accessToken)
            ->postJson('/api/game-tickets')
            ->assertStatus(409);

        $this->assertDatabaseCount('game_tickets', 0);
    }

    public function test_a_ticket_cannot_be_consumed_after_its_character_becomes_inactive(): void
    {
        $user = User::factory()->create();
        $character = $user->characters()->create([
            'name' => 'UNIT#ABC-123-XYZ',
            'status' => 'active',
        ]);
        $accessToken = $user->createToken('test-client', ['game-ticket:create'])->plainTextToken;

        $plainTextTicket = $this->withToken($accessToken)
            ->postJson('/api/game-tickets')
            ->assertCreated()
            ->json('data.ticket');

        $character->update(['status' => 'inactive']);

        $this->withToken('test-game-server-token')
            ->postJson('/api/internal/game-tickets/consume', ['ticket' => $plainTextTicket])
            ->assertUnauthorized();

        $this->assertNull(GameTicket::query()->firstOrFail()->consumed_at);
    }

    public function test_the_prune_command_deletes_only_expired_tickets(): void
    {
        $user = User::factory()->create();

        $expiredTicket = GameTicket::create([
            'user_id' => $user->id,
            'token_hash' => str_repeat('a', 64),
            'expires_at' => now()->subSecond(),
        ]);

        $activeTicket = GameTicket::create([
            'user_id' => $user->id,
            'token_hash' => str_repeat('b', 64),
            'expires_at' => now()->addMinute(),
        ]);

        $this->artisan('model:prune', [
            '--model' => [GameTicket::class],
        ])->assertSuccessful();

        $this->assertDatabaseMissing('game_tickets', ['id' => $expiredTicket->id]);
        $this->assertDatabaseHas('game_tickets', ['id' => $activeTicket->id]);
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
