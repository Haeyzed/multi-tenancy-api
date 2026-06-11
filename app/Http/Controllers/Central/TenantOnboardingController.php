<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\OnboardTenantRequest;
use App\Http\Resources\Central\TenantResource;
use App\Services\Central\TenantOnboardingService;
use Illuminate\Http\JsonResponse;

/**
 * Tenant onboarding workflow.
 */
class TenantOnboardingController extends Controller
{
    public function __construct(
        private readonly TenantOnboardingService $service,
    )
    {
    }

    /**
     * Onboard a new tenant with domain and subscription.
     *
     * @param OnboardTenantRequest $request Validated onboarding payload.
     */
    public function store(OnboardTenantRequest $request): JsonResponse
    {
        $tenant = $this->service->onboard($request->validated());

        return $this->created(
            new TenantResource($tenant->load(['plan', 'domains', 'activeSubscription'])),
            'Tenant onboarded successfully.',
        );
    }
}
