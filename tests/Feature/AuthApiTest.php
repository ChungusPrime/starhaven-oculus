<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_player_can_register_without_creating_an_access_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test Player',
            'email' => 'player@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertExactJson(['success' => true]);

        $this->assertDatabaseHas('users', ['email' => 'player@example.com']);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_a_player_can_login_and_inspect_their_session(): void
    {
        User::factory()->create([
            'email' => 'player@example.com',
            'password' => 'password123',
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'player@example.com',
            'password' => 'password123',
            'device_name' => 'test-client',
        ])->assertOk();

        $this->withToken($login->json('data.token.access_token'))
            ->getJson('/api/auth/session')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'player@example.com');
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
