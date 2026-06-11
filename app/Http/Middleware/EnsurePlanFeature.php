<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use App\Services\Central\PlanEntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the tenant's plan includes a required feature (plan_features rows only).
 */
class EnsurePlanFeature
{
    public function __construct(
        private readonly PlanEntitlementService $entitlements,
    )
    {
    }

    /**
     * @param Closure(Request): Response $next
     * @param string $feature Feature key required for the route.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        /** @var Tenant|null $tenant */
        $tenant = tenant();

        if ($tenant === null) {
            return response()->json(['message' => 'Tenant context not initialized.'], 403);
        }

        if (!$this->entitlements->hasFeature($tenant, $feature)) {
            return response()->json([
                'message' => 'This feature is not available on your current plan.',
                'feature' => $feature,
            ], 403);
        }

        return $next($request);
    }
}
