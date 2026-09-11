<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('oculus.admin.username', 'oculus_admin');
        config()->set('oculus.admin.password', 'correct-horse-battery-staple');
    }

    public function test_admin_login_page_is_available(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertViewIs('admin.login');
    }

    public function test_guests_are_redirected_from_the_admin_dashboard(): void
    {
        $this->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));
    }

    public function test_valid_credentials_create_an_admin_session(): void
    {
        $this->post('/admin', [
            'username' => 'oculus_admin',
            'password' => 'correct-horse-battery-staple',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(session('admin.authenticated'));
        $this->assertSame('oculus_admin', session('admin.username'));
        $this->assertNotEmpty(session('admin.signature'));

        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Command network');
    }

    public function test_invalid_credentials_do_not_create_an_admin_session(): void
    {
        $this->from('/admin')->post('/admin', [
            'username' => 'oculus_admin',
            'password' => 'incorrect',
        ])->assertRedirect('/admin')
            ->assertSessionHasErrors('username');

        $this->assertNull(session('admin.authenticated'));
    }

    public function test_changing_credentials_invalidates_an_existing_admin_session(): void
    {
        $this->post('/admin', [
            'username' => 'oculus_admin',
            'password' => 'correct-horse-battery-staple',
        ]);

        config()->set('oculus.admin.password', 'rotated-secret');

        $this->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));
        $this->assertNull(session('admin.authenticated'));
    }

    public function test_an_admin_can_log_out(): void
    {
        $this->post('/admin', [
            'username' => 'oculus_admin',
            'password' => 'correct-horse-battery-staple',
        ]);

        $this->post('/admin/logout')
            ->assertRedirect(route('admin.login'));

        $this->get('/admin/dashboard')
            ->assertRedirect(route('admin.login'));
    }
}