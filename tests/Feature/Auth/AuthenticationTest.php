<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_active_admin_can_login_using_username(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        $response = $this->post('/login', [
            'username' => 'admin_test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_admin_can_not_login_with_invalid_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Test',
            'username' => 'admin_test',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
    }

    public function test_inactive_admin_can_not_login(): void
    {
        /** @var User $user */
        $user = User::factory()->inactive()->create([
            'nama_user' => 'Admin Nonaktif',
            'username' => 'admin_nonaktif',
            'email' => 'admin_nonaktif@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
    }

    public function test_authenticated_admin_can_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'nama_user' => 'Admin Logout',
            'username' => 'admin_logout',
            'email' => 'admin_logout@example.com',
            'password' => bcrypt('password'),
            'status_aktif' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();

        $response->assertRedirect('/');
    }

    public function test_guest_can_not_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
