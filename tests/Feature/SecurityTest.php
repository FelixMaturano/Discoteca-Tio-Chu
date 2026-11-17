<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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
        $response->assertHeader('Content-Security-Policy');
        $contentSecurityPolicy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $contentSecurityPolicy);
        $this->assertStringContainsString(
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            $contentSecurityPolicy
        );
        $this->assertStringContainsString(
            'style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://fonts.bunny.net',
            $contentSecurityPolicy
        );
        $this->assertStringContainsString(
            'font-src \'self\' data: https://fonts.gstatic.com https://fonts.bunny.net',
            $contentSecurityPolicy
        );
        $this->assertStringContainsString('img-src \'self\' data: blob: https:', $contentSecurityPolicy);
        $this->assertStringContainsString("connect-src 'self'", $contentSecurityPolicy);
        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_local_content_security_policy_allows_vite_assets_and_hmr(): void
    {
        $this->app->detectEnvironment(static fn () => 'local');

        try {
            $response = $this->get('/login');
            $contentSecurityPolicy = $response->headers->get('Content-Security-Policy');

            $this->assertStringContainsString(
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173",
                $contentSecurityPolicy
            );
            $this->assertStringContainsString(
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173",
                $contentSecurityPolicy
            );
            $this->assertStringContainsString(
                "connect-src 'self' http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173 ws://localhost:5173 ws://127.0.0.1:5173 ws://[::1]:5173",
                $contentSecurityPolicy
            );
        } finally {
            $this->app->detectEnvironment(static fn () => 'testing');
        }
    }

    public function test_production_app_provider_forces_https_urls(): void
    {
        URL::forceScheme(null);
        $this->app->detectEnvironment(static fn () => 'production');

        try {
            (new AppServiceProvider($this->app))->boot();

            $this->assertStringStartsWith('https://', URL::to('/login'));
        } finally {
            $this->app->detectEnvironment(static fn () => 'testing');
            URL::forceScheme(null);
        }
    }

    public function test_hsts_is_only_sent_for_https_requests(): void
    {
        $response = $this->get('https://localhost/login');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_forwarded_https_from_untrusted_proxy_does_not_enable_hsts(): void
    {
        $response = $this->get('http://localhost/login', [
            'X-Forwarded-Proto' => 'https',
        ]);

        $response->assertOk();
        $response->assertHeaderMissing('Strict-Transport-Security');
    }
}
