<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Central\TenantStatus;
use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the resolved tenant is in an active lifecycle state.
 */
class EnsureTenantIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Tenant|null $tenant */
        $tenant = tenant();

        if ($tenant === null) {
            return response()->json(['message' => 'Tenant context not initialized.'], 403);
        }

        if ($tenant->status !== TenantStatus::Active) {
            return response()->json([
                'message' => 'Tenant account is not active.',
                'status' => $tenant->status->value,
            ], 403);
        }

        return $next($request);
    }
}
