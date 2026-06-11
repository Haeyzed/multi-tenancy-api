<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Central\SubscriptionStatus;
use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the tenant has an active or trialing subscription.
 */
class EnsureSubscriptionIsActive
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Tenant|null $tenant */
        $tenant = tenant();

        if ($tenant === null) {
            return response()->json(['message' => 'Tenant context not initialized.'], 403);
        }

        $subscription = $tenant->activeSubscription;

        if ($subscription === null) {
            return response()->json(['message' => 'No active subscription found.'], 403);
        }

        if (!in_array($subscription->status, [SubscriptionStatus::Active, SubscriptionStatus::Trialing], true)) {
            return response()->json([
                'message' => 'Subscription is not active.',
                'status' => $subscription->status->value,
            ], 403);
        }

        return $next($request);
    }
}
