<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next, ?string $environment = null): Response
    {
        if ($environment !== null && ! app()->environment($environment)) {
            abort(403);
        }

        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
