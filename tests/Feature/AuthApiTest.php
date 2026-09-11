<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_available(): void
    {
        foreach (['/', '/register'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertViewIs('auth.register');
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
        ])->assertOk();

        $this->withToken($login->json('data.token.access_token'))
            ->getJson('/api/auth/session')
            ->assertOk()
            ->assertJsonPath('data.user.username', 'test_player');
    }

    public function test_a_player_can_create_a_character(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-client')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/auth/create_character', [
                'character_name' => 'Nova',
                'character_class' => '3',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('character.name', 'Nova')
            ->assertJsonPath('character.user_id', $user->id);

        $character = $user->characters()->sole();

        $this->assertSame('Nova', $character->name);
        $this->assertSame('3', json_decode($character->data, true)['class']);
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
