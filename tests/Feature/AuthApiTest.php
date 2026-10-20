<?php

namespace Tests\Feature;

use App\Models\Universe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_status_is_available(): void
    {
        $this->getJson('/api/status')
            ->assertOk()
            ->assertExactJson(['success' => true]);
    }

    public function test_registration_page_is_available(): void
    {
        foreach (['/', '/register'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertInertia(
                    fn (Assert $page) => $page->component('Register'),
                );
        }
    }

    public function test_a_player_can_register_without_creating_an_access_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'Test_Player',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertExactJson(['success' => true]);

        $this->assertDatabaseHas('users', ['username' => 'test_player']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_a_username_must_be_unique(): void
    {
        User::factory()->create(['username' => 'test_player']);

        $this->postJson('/api/auth/register', [
            'username' => 'Test_Player',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    public function test_a_player_can_login_and_inspect_their_session(): void
    {
        User::factory()->create([
            'username' => 'test_player',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/auth/login', [
            'username' => 'Test_Player',
            'password' => 'password123',
            'device_name' => 'test-client',
        ])->assertOk()
            ->assertJsonPath('data.universes.0.name', 'Universe Alpha')
            ->assertJsonPath('data.universes.0.status', 'offline');

        $this->withToken($login->json('data.token.access_token'))
            ->getJson('/api/auth/session')
            ->assertOk()
            ->assertJsonPath('data.user.username', 'test_player');
    }

    public function test_login_returns_configured_universes_with_latest_database_stats(): void
    {
        config()->set('oculus.universes', [
            ['name' => 'Universe Gamma', 'address' => 'https://gamma.example.test'],
            ['name' => 'Universe Alpha', 'address' => 'https://alpha.example.test'],
            ['name' => 'Universe Delta', 'address' => 'https://delta.example.test'],
        ]);

        Universe::query()->where('name', 'Universe Alpha')->firstOrFail()->update([
            'players' => 17,
            'online' => true,
            'last_refresh' => '2026-09-28 12:34:56',
        ]);

        User::factory()->create([
            'username' => 'test_player',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'username' => 'test_player',
            'password' => 'password123',
            'device_name' => 'test-client',
        ])->assertOk()
            ->assertJsonCount(3, 'data.universes')
            ->assertJsonPath('data.universes.0.name', 'Universe Gamma')
            ->assertJsonPath('data.universes.1.name', 'Universe Alpha')
            ->assertJsonPath('data.universes.1.address', 'https://alpha.example.test')
            ->assertJsonPath('data.universes.1.players', 17)
            ->assertJsonPath('data.universes.1.online', true)
            ->assertJsonPath('data.universes.1.status', 'online')
            ->assertJsonPath('data.universes.1.last_refresh', '2026-09-28T12:34:56.000000Z')
            ->assertJsonPath('data.universes.2.name', 'Universe Delta')
            ->assertJsonPath('data.universes.2.players', 0)
            ->assertJsonPath('data.universes.2.online', false)
            ->assertJsonPath('data.universes.2.status', 'offline')
            ->assertJsonPath('data.universes.2.last_refresh', null);
    }

    public function test_a_player_can_create_a_character(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-client')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/characters')
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('character.status', 'active')
            ->assertJsonPath('character.user_id', $user->id);

        $character = $user->characters()->sole();

        $this->assertMatchesRegularExpression('/^UNIT#[A-Z0-9]{3}-[A-Z0-9]{3}-[A-Z0-9]{3}$/', $response->json('character.name'));
        $this->assertSame('active', $character->status);
        $this->assertSame('Starhaven Pioneer Base', $character->area);
    }

    public function test_a_player_cannot_create_a_second_active_character(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-client')->plainTextToken;

        $this->withToken($token)->postJson('/api/characters')->assertCreated();
        $this->withToken($token)
            ->postJson('/api/characters')
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $this->assertSame(1, $user->characters()->where('status', 'active')->count());
    }

    public function test_an_inactive_character_does_not_block_character_creation(): void
    {
        $user = User::factory()->create();
        $user->characters()->create([
            'name' => 'UNIT#OLD-000-001',
            'status' => 'inactive',
        ]);
        $token = $user->createToken('test-client')->plainTextToken;

        $this->withToken($token)->postJson('/api/characters')->assertCreated();

        $this->assertSame(2, $user->characters()->count());
        $this->assertSame(1, $user->characters()->where('status', 'active')->count());
    }

    public function test_login_returns_one_active_character_instead_of_a_list(): void
    {
        $user = User::factory()->create([
            'username' => 'test_player',
            'password' => 'password123',
        ]);
        $user->characters()->create([
            'name' => 'UNIT#OLD-000-001',
            'status' => 'inactive',
        ]);
        $activeCharacter = $user->characters()->create([
            'name' => 'UNIT#NEW-000-001',
            'status' => 'active',
        ]);

        $this->postJson('/api/auth/login', [
            'username' => 'test_player',
            'password' => 'password123',
            'device_name' => 'test-client',
        ])->assertOk()
            ->assertJsonPath('data.character.id', $activeCharacter->id)
            ->assertJsonPath('data.character.status', 'active')
            ->assertJsonMissingPath('data.characters');
    }

    public function test_updating_a_session_rotates_the_current_token(): void
    {
        $user = User::factory()->create();
        $oldToken = $user->createToken('old-client', ['game-ticket:create'])->plainTextToken;

        $rotated = $this->withToken($oldToken)
            ->putJson('/api/auth/session', ['device_name' => 'new-client'])
            ->assertOk();

        $newToken = $rotated->json('data.token.access_token');

        $this->app['auth']->forgetGuards();
        $this->withToken($oldToken)->getJson('/api/auth/session')->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($newToken)->getJson('/api/auth/session')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_a_player_can_log_out_the_current_session(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-client')->plainTextToken;

        $this->withToken($token)
            ->deleteJson('/api/auth/session')
            ->assertNoContent();

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/auth/session')->assertUnauthorized();
    }
}
