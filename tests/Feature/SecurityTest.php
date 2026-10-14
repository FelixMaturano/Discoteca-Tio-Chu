<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'DonLudo@gmail.com')->first();
    }

    public function test_guest_accessing_root_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_guest_accessing_dashboard_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_guest_accessing_protected_modules_is_redirected_to_login(): void
    {
        $this->get('/products')->assertRedirect('/login');
        $this->get('/staff')->assertRedirect('/login');
        $this->get('/sales')->assertRedirect('/login');
        $this->get('/closing')->assertRedirect('/login');
    }

    public function test_authenticated_user_accessing_root_is_redirected_to_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/');
        $response->assertRedirect('/dashboard');
    }

    public function test_login_rate_limiting_protects_against_brute_force(): void
    {
        // 5 intentos fallidos
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'fake@email.com',
                'password' => 'wrongpassword',
            ]);
        }

        // El 6to intento debe ser bloqueado por Rate Limiting con HTTP 429
        $response = $this->post('/login', [
            'email' => 'fake@email.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/login');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
