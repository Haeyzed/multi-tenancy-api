<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sanctum bearer tokens authenticate via the request user, but Spatie
 * permission checks use the tenant guard. Sync the user after auth:sanctum
 * when routes still use the default Sanctum guard.
 *
 * Prefer `auth:tenant` on tenant routes; this middleware is a safety net.
 */
class UseTenantAuthGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            Auth::shouldUse('tenant');
            Auth::guard('tenant')->setUser($user);
        }

        return $next($request);
    }
}
