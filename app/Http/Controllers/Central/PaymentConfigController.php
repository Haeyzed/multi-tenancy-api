<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Public payment provider configuration for signup frontends.
 */
class PaymentConfigController extends Controller
{
    /**
     * Return publishable keys for supported payment providers.
     */
    public function index(): JsonResponse
    {
        return $this->success([
            'stripe' => [
                'public_key' => config('payments.stripe.key'),
            ],
            'paystack' => [
                'public_key' => config('payments.paystack.public_key'),
            ],
            'checkout' => [
                'callback_url' => config('payments.checkout.success_url'),
                'frontend_success_url' => config('payments.checkout.frontend_success_url'),
                'cancel_url' => config('payments.checkout.cancel_url'),
            ],
            'trial_setup_amount' => (int)config('payments.trial_setup_amount', 10_000),
        ], 'Payment configuration retrieved successfully.');
    }
}
