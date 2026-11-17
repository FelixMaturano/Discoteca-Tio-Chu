<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Inyecta encabezados HTTP de seguridad estándar (OWASP) para mitigar
     * Clickjacking, MIME-sniffing, XSS e inyecciones.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $isLocal = app()->environment('local');
        $localViteSources = $isLocal
            ? ['http://localhost:5173', 'http://127.0.0.1:5173', 'http://[::1]:5173']
            : [];
        $localViteConnectSources = array_merge(
            $localViteSources,
            ['ws://localhost:5173', 'ws://127.0.0.1:5173', 'ws://[::1]:5173']
        );
        $connectSources = $isLocal
            ? array_merge(["'self'"], $localViteConnectSources)
            : ["'self'"];

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
                "object-src 'none'",
                'script-src '.implode(' ', array_merge(
                    ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https://cdnjs.cloudflare.com'],
                    $localViteSources
                )),
                'style-src '.implode(' ', array_merge(
                    ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://fonts.bunny.net'],
                    $localViteSources
                )),
                "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
                "img-src 'self' data: blob: https:",
                'connect-src '.implode(' ', $connectSources),
            ])
        );

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
