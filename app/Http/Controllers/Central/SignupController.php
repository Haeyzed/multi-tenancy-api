<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Enums\Central\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\SelfServiceSignupRequest;
use App\Http\Requests\Central\SignupCheckoutRequest;
use App\Http\Resources\Central\TenantResource;
use App\Models\Central\Tenant;
use App\Services\Central\SelfServiceSignupService;
use Illuminate\Http\JsonResponse;

/**
 * Self-service tenant registration and checkout.
 */
class SignupController extends Controller
{
    public function __construct(
        private readonly SelfServiceSignupService $service,
    ) {}

    /**
     * Register a new tenant and optionally redirect to payment.
     */
    public function store(SelfServiceSignupRequest $request): JsonResponse
    {
        $result = $this->service->signup($request->validated());

        $message = match (true) {
            $result['requires_payment'] => 'Signup initiated. Complete payment to activate your account.',
            $result['requires_payment_method'] => 'Signup completed. Add a payment method to continue after your trial.',
            default => 'Signup completed. Your trial has started.',
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
     * Create a new checkout session for a pending signup.
     */
    public function checkout(SignupCheckoutRequest $request, Tenant $tenant): JsonResponse
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
