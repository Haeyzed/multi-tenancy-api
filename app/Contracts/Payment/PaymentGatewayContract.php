<?php

declare(strict_types=1);

namespace App\Contracts\Payment;

use App\Data\Payment\CheckoutResult;
use App\Enums\Central\BillingCycle;
use App\Models\Central\Invoice;
use App\Models\Central\Plan;
use App\Models\Central\Tenant;

/**
 * Payment gateway adapter for checkout and webhook verification.
 */
interface PaymentGatewayContract
{
    /**
     * Create a hosted checkout session for an invoice.
     */
    public function createCheckout(
        Invoice $invoice,
        Tenant $tenant,
        Plan $plan,
        BillingCycle $billingCycle,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutResult;

    /**
     * Verify an incoming webhook request is authentic.
     */
    public function verifyWebhook(string $payload, ?string $signature): bool;

    /**
     * Extract invoice ID from a verified webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function extractInvoiceIdFromWebhook(array $payload): ?string;

    /**
     * Extract provider payment reference from a verified webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function extractProviderPaymentIdFromWebhook(array $payload): ?string;
}
