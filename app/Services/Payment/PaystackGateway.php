<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Contracts\Payment\RecurringPaymentGatewayContract;
use App\Data\Payment\ChargeResult;
use App\Data\Payment\CheckoutResult;
use App\Enums\Central\BillingCycle;
use App\Models\Central\Invoice;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Paystack transaction and authorization charge adapter.
 */
class PaystackGateway implements RecurringPaymentGatewayContract
{
    private const API_BASE = 'https://api.paystack.co';

    /**
     * {@inheritDoc}
     */
    public function createCheckout(
        Invoice $invoice,
        Tenant $tenant,
        Plan $plan,
        BillingCycle $billingCycle,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutResult {
        return $this->initializeTransaction(
            $invoice,
            $tenant,
            $invoice->amount_due,
            $successUrl,
            $cancelUrl,
            [
                'plan_name' => $plan->name,
                'billing_cycle' => $billingCycle->value,
                'purpose' => 'payment',
            ],
        );
    }

    /**
     * {@inheritDoc}
     */
    public function createSetupCheckout(
        Tenant $tenant,
        Subscription $subscription,
        string $successUrl,
        string $cancelUrl,
    ): CheckoutResult {
        $tenant->loadMissing('plan');

        $amount = max(
            (int) config('payments.trial_setup_amount', 10_000),
            (int) config('payments.paystack.min_amount', 10_000),
        );

        return $this->initializeTransaction(
            null,
            $tenant,
            $amount,
            $successUrl,
            $cancelUrl,
            [
                'subscription_id' => $subscription->id,
                'purpose' => 'trial_setup',
            ],
            $subscription,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function chargeSavedMethod(
        Invoice $invoice,
        Tenant $tenant,
        Subscription $subscription,
    ): ChargeResult {
        $tenant->loadMissing('paymentMethods');

        $method = $tenant->paymentMethods()->where('is_default', true)->first();

        $authorizationCode = $method?->billing_details['authorization_code'] ?? null;

        if ($authorizationCode === null) {
            return ChargeResult::failed('No saved Paystack authorization on file.');
        }

        $reference = sprintf('renew_%s_%s', str_replace('-', '', $invoice->id), Str::lower(Str::random(8)));

        $response = Http::withToken((string) config('payments.paystack.secret_key'))
            ->acceptJson()
            ->post(self::API_BASE.'/transaction/charge_authorization', [
                'authorization_code' => $authorizationCode,
                'email' => $tenant->owner_email,
                'amount' => $invoice->amount_due,
                'currency' => strtoupper($invoice->currency),
                'reference' => $reference,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'invoice_id' => $invoice->id,
                ],
            ]);

        if ($response->successful() && $response->json('data.status') === 'success') {
            return ChargeResult::succeeded((string) $response->json('data.reference', $reference));
        }

        return ChargeResult::failed($response->json('message') ?? $response->body());
    }

    /**
     * {@inheritDoc}
     */
    public function verifyWebhook(string $payload, ?string $signature): bool
    {
        if ($signature === null || $signature === '') {
            return false;
        }

        $secret = (string) config('payments.paystack.secret_key');
        $computed = hash_hmac('sha512', $payload, $secret);

        return hash_equals($computed, $signature);
    }

    /**
     * Verify a transaction with Paystack after redirect from checkout.
     *
     * @return array<string, mixed> Paystack charge object (`data` from verify response).
     */
    public function verifyTransaction(string $reference): array
    {
        $secretKey = (string) config('payments.paystack.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('Paystack is not configured. Set PAYSTACK_SECRET_KEY in your environment.');
        }

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->get(self::API_BASE.'/transaction/verify/'.urlencode($reference));

        if (! $response->successful() || ! ($response->json('status') === true)) {
            throw new RuntimeException(
                'Paystack verification failed: '.($response->json('message') ?? $response->body()),
            );
        }

        $charge = $response->json('data');

        if (! is_array($charge) || ($charge['status'] ?? null) !== 'success') {
            $status = is_array($charge) ? (string) ($charge['status'] ?? 'unknown') : 'unknown';

            throw new RuntimeException("Paystack transaction was not successful (status: {$status}).");
        }

        return $charge;
    }

    /**
     * {@inheritDoc}
     */
    public function extractInvoiceIdFromWebhook(array $payload): ?string
    {
        if (($payload['event'] ?? null) !== 'charge.success') {
            return null;
        }

        if (($payload['data']['metadata']['purpose'] ?? 'payment') !== 'payment') {
            return null;
        }

        return $payload['data']['metadata']['invoice_id'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function extractProviderPaymentIdFromWebhook(array $payload): ?string
    {
        if (($payload['event'] ?? null) !== 'charge.success') {
            return null;
        }

        return $payload['data']['reference'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function extractPaymentMethodFromWebhook(array $payload): ?array
    {
        if (($payload['event'] ?? null) !== 'charge.success') {
            return null;
        }

        $data = $payload['data'] ?? [];
        $authorization = $data['authorization'] ?? null;

        if ($authorization === null) {
            return null;
        }

        return [
            'provider_customer_id' => (string) ($data['customer']['id'] ?? $data['customer']['customer_code'] ?? ''),
            'provider_method_id' => (string) ($authorization['authorization_code'] ?? $data['reference']),
            'purpose' => $data['metadata']['purpose'] ?? 'payment',
            'subscription_id' => $data['metadata']['subscription_id'] ?? null,
            'tenant_id' => $data['metadata']['tenant_id'] ?? null,
            'type' => 'card',
            'last4' => $authorization['last4'] ?? null,
            'brand' => $authorization['brand'] ?? $authorization['card_type'] ?? null,
            'exp_month' => isset($authorization['exp_month']) ? (int) $authorization['exp_month'] : null,
            'exp_year' => isset($authorization['exp_year']) ? (int) $authorization['exp_year'] : null,
            'billing_details' => [
                'authorization_code' => $authorization['authorization_code'] ?? null,
                'bin' => $authorization['bin'] ?? null,
                'bank' => $authorization['bank'] ?? null,
            ],
        ];
    }

    /**
     * Whether the webhook is a trial setup charge.
     *
     * @param  array<string, mixed>  $payload
     */
    public function isSetupWebhook(array $payload): bool
    {
        if (($payload['event'] ?? null) !== 'charge.success') {
            return false;
        }

        return ($payload['data']['metadata']['purpose'] ?? null) === 'trial_setup';
    }

    /**
     * @param  array<string, mixed>  $extraMetadata
     */
    private function initializeTransaction(
        ?Invoice $invoice,
        Tenant $tenant,
        int $amount,
        string $successUrl,
        string $cancelUrl,
        array $extraMetadata = [],
        ?Subscription $subscription = null,
    ): CheckoutResult {
        $secretKey = (string) config('payments.paystack.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('Paystack is not configured. Set PAYSTACK_SECRET_KEY in your environment.');
        }

        $tenant->loadMissing('plan');
        $currency = strtoupper($invoice?->currency ?? $tenant->plan?->currency ?? 'NGN');
        $minAmount = (int) config('payments.paystack.min_amount', 10_000);

        if ($currency === 'NGN' && $amount < $minAmount) {
            throw new RuntimeException(sprintf(
                'Paystack amount too low: %d kobo (minimum %d kobo / ₦%.2f).',
                $amount,
                $minAmount,
                $minAmount / 100,
            ));
        }

        $reference = sprintf(
            'txn_%s_%s',
            $invoice !== null ? str_replace('-', '', $invoice->id) : str_replace('-', '', $tenant->id),
            Str::lower(Str::random(8)),
        );

        $metadata = array_merge([
            'tenant_id' => $tenant->id,
            'subscription_id' => (string) ($invoice?->subscription_id ?? $subscription?->id),
            'invoice_id' => $invoice?->id,
            'cancel_url' => $cancelUrl,
        ], $extraMetadata);

        $payload = [
            'email' => $tenant->owner_email,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $reference,
            'callback_url' => rtrim($successUrl, '/'),
            'metadata' => $metadata,
        ];

        $channels = config('payments.paystack.channels', ['card']);

        if (is_array($channels) && $channels !== []) {
            $payload['channels'] = $channels;
        }

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->post(self::API_BASE.'/transaction/initialize', $payload);

        if (! $response->successful() || ! ($response->json('status') === true)) {
            $message = (string) ($response->json('message') ?? $response->body());

            if (str_contains(strtolower($message), 'no active channel')) {
                $message .= ' Enable Card under Paystack Dashboard → Settings → Payment Channels, '
                    .'confirm your account is activated, and use test keys (sk_test_/pk_test_) in development.';
            }

            throw new RuntimeException('Paystack checkout failed: '.$message);
        }

        return new CheckoutResult(
            checkoutUrl: (string) $response->json('data.authorization_url'),
            reference: (string) $response->json('data.reference', $reference),
        );
    }
}
