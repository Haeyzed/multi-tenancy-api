<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Enums\Central\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\SelfOnboardingCheckoutRequest;
use App\Http\Requests\Central\SelfOnboardingRequest;
use App\Http\Resources\Central\TenantResource;
use App\Models\Central\Tenant;
use App\Services\Central\SelfOnboardingService;
use Illuminate\Http\JsonResponse;

/**
 * Self-service tenant onboarding and checkout.
 */
class SelfOnboardingController extends Controller
{
    public function __construct(
        private readonly SelfOnboardingService $service,
    )
    {
    }

    /**
     * Register a new tenant and optionally redirect to payment.
     */
    public function store(SelfOnboardingRequest $request): JsonResponse
    {
        set_time_limit((int)config('tenancy.self_onboarding_max_execution_time', 600));

        $result = $this->service->onboard($request->validated());

        $message = match (true) {
            $result['requires_payment'] => 'Self-onboarding initiated. Complete payment to activate your account.',
            $result['requires_payment_method'] => 'Self-onboarding started. Add a payment method to continue after your trial.',
            default => 'Self-onboarding completed. Your trial has started.',
        };

        return $this->created([
            'tenant' => new TenantResource($result['tenant']),
            'requires_payment' => $result['requires_payment'],
            'requires_payment_method' => $result['requires_payment_method'],
            'checkout_url' => $result['checkout_url'],
            'payment_provider' => $result['payment_provider'],
            'invoice_id' => $result['invoice_id'],
        ], $message);
    }

    /**
     * Create a new checkout session for a pending self-onboarding tenant.
     */
    public function checkout(SelfOnboardingCheckoutRequest $request, Tenant $tenant): JsonResponse
    {
        $provider = PaymentProvider::from($request->string('payment_provider')->toString());

        $validated = $request->validated();

        $result = $this->service->checkout(
            $tenant,
            $provider,
            $validated['success_url'] ?? null,
            $validated['cancel_url'] ?? null,
        );

        return $this->success($result, 'Checkout session created.');
    }
}
