<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_log_in_and_logout(): void
    {
        User::create([
            'name' => 'Petugas Uji',
            'email' => 'petugas@example.test',
            'password' => 'password-rahasia',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'petugas@example.test',
            'password' => 'password-rahasia',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->get('/dashboard')->assertOk();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        User::create([
            'name' => 'Akun Nonaktif',
            'email' => 'nonaktif@example.test',
            'password' => 'password-rahasia',
            'role' => 'anggota',
            'is_active' => false,
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'nonaktif@example.test',
            'password' => 'password-rahasia',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_cannot_open_route_for_another_role(): void
    {
        $user = User::create([
            'name' => 'Anggota Uji',
            'email' => 'anggota@example.test',
            'password' => 'password-rahasia',
            'role' => 'anggota',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_login_throttles_repeated_failed_attempts(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $response = $this->from('/login')->post('/login', [
                'email' => 'missing@example.test',
                'password' => 'salah',
            ]);
        }

        $response->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
