<?php

declare(strict_types=1);

namespace App\Contracts\Payment;

use App\Data\Payment\ChargeResult;
use App\Data\Payment\CheckoutResult;
use App\Models\Central\Invoice;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;

/**
 * Payment gateway support for saved methods and off-session charges.
 */
interface RecurringPaymentGatewayContract extends PaymentGatewayContract
{
    /**
     * Collect and save a payment method without charging (Stripe setup mode).
     */
    public function createSetupCheckout(
        Tenant $tenant,
        Subscription $subscription,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutResult;

    /**
     * Attempt to charge a saved payment method for an invoice.
     */
    public function chargeSavedMethod(
        Invoice $invoice,
        Tenant $tenant,
        Subscription $subscription,
    ): ChargeResult;

    /**
     * Persist payment method details from a webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function extractPaymentMethodFromWebhook(array $payload): ?array;
}
