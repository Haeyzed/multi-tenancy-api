<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentGatewayContract;
use App\Contracts\Payment\RecurringPaymentGatewayContract;
use App\Enums\Central\PaymentProvider;
use InvalidArgumentException;

/**
 * Resolve payment gateway adapters by provider.
 */
class PaymentGatewayManager
{
    public function __construct(
        private readonly StripeGateway $stripe,
        private readonly PaystackGateway $paystack,
    ) {}

    /**
     * Get the gateway adapter for a payment provider.
     */
    public function gateway(PaymentProvider $provider): PaymentGatewayContract
    {
        return match ($provider) {
            PaymentProvider::Stripe => $this->stripe,
            PaymentProvider::Paystack => $this->paystack,
            default => throw new InvalidArgumentException("Payment provider [{$provider->value}] is not supported for checkout."),
        };
    }

    /**
     * Get a recurring-capable gateway adapter.
     */
    public function recurring(PaymentProvider $provider): RecurringPaymentGatewayContract
    {
        $gateway = $this->gateway($provider);

        if (! $gateway instanceof RecurringPaymentGatewayContract) {
            throw new InvalidArgumentException("Payment provider [{$provider->value}] does not support recurring charges.");
        }

        return $gateway;
    }
}
