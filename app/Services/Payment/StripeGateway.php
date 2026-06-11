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
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

/**
 * Stripe Checkout and off-session billing adapter.
 */
class StripeGateway implements RecurringPaymentGatewayContract
{
    public function __construct()
    {
        Stripe::setApiKey(config('payments.stripe.secret'));
    }

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
        $session = Session::create([
            'mode' => 'payment',
            'customer_creation' => 'always',
            'customer_email' => $tenant->owner_email,
            'payment_intent_data' => [
                'setup_future_usage' => 'off_session',
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($invoice->currency),
                    'unit_amount' => $invoice->amount_due,
                    'product_data' => [
                        'name' => $plan->name,
                        'description' => "{$plan->name} ({$billingCycle->value})",
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => rtrim($successUrl, '/').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'tenant_id' => $tenant->id,
                'subscription_id' => (string) $invoice->subscription_id,
                'invoice_id' => $invoice->id,
                'purpose' => 'payment',
            ],
        ]);

        return new CheckoutResult(
            checkoutUrl: (string) $session->url,
            reference: (string) $session->id,
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
        $session = Session::create([
            'mode' => 'setup',
            'customer_creation' => 'always',
            'customer_email' => $tenant->owner_email,
            'success_url' => rtrim($successUrl, '/').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'purpose' => 'trial_setup',
            ],
        ]);

        return new CheckoutResult(
            checkoutUrl: (string) $session->url,
            reference: (string) $session->id,
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
        if ($subscription->payment_provider_id === null || $subscription->payment_method_id === null) {
            return ChargeResult::failed('No saved Stripe payment method on file.');
        }

        try {
            $intent = PaymentIntent::create([
                'amount' => $invoice->amount_due,
                'currency' => strtolower($invoice->currency),
                'customer' => $subscription->payment_provider_id,
                'payment_method' => $subscription->payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'invoice_id' => $invoice->id,
                ],
            ]);

            if ($intent->status === 'succeeded') {
                return ChargeResult::succeeded((string) $intent->id);
            }

            return ChargeResult::failed('Payment intent status: '.$intent->status);
        } catch (ApiErrorException $exception) {
            return ChargeResult::failed($exception->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function verifyWebhook(string $payload, ?string $signature): bool
    {
        if ($signature === null || $signature === '') {
            return false;
        }

        try {
            Webhook::constructEvent(
                $payload,
                $signature,
                (string) config('payments.stripe.webhook_secret'),
            );

            return true;
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function extractInvoiceIdFromWebhook(array $payload): ?string
    {
        if (($payload['type'] ?? null) !== 'checkout.session.completed') {
            return null;
        }

        $purpose = $payload['data']['object']['metadata']['purpose'] ?? 'payment';

        if ($purpose !== 'payment') {
            return null;
        }

        return $payload['data']['object']['metadata']['invoice_id'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function extractProviderPaymentIdFromWebhook(array $payload): ?string
    {
        if (($payload['type'] ?? null) !== 'checkout.session.completed') {
            return null;
        }

        $object = $payload['data']['object'] ?? [];

        return $object['payment_intent'] ?? $object['id'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function extractPaymentMethodFromWebhook(array $payload): ?array
    {
        if (($payload['type'] ?? null) !== 'checkout.session.completed') {
            return null;
        }

        $object = $payload['data']['object'] ?? [];
        $setupIntent = $object['setup_intent'] ?? null;
        $paymentIntent = $object['payment_intent'] ?? null;

        if ($setupIntent === null && $paymentIntent === null) {
            return null;
        }

        return [
            'provider_customer_id' => $object['customer'] ?? null,
            'provider_method_id' => $object['payment_method'] ?? null,
            'purpose' => $object['metadata']['purpose'] ?? 'payment',
            'subscription_id' => $object['metadata']['subscription_id'] ?? null,
            'tenant_id' => $object['metadata']['tenant_id'] ?? null,
            'type' => 'card',
            'setup_intent' => $setupIntent,
            'payment_intent' => $paymentIntent,
        ];
    }

    /**
     * Whether the webhook is a trial setup session completion.
     *
     * @param  array<string, mixed>  $payload
     */
    public function isSetupWebhook(array $payload): bool
    {
        if (($payload['type'] ?? null) !== 'checkout.session.completed') {
            return false;
        }

        return ($payload['data']['object']['metadata']['purpose'] ?? null) === 'trial_setup';
    }

    /**
     * Resolve saved payment method details from a completed setup session.
     *
     * @return array<string, mixed>|null
     */
    public function resolveSetupDetails(string $sessionId): ?array
    {
        $session = Session::retrieve($sessionId, ['expand' => ['setup_intent']]);
        $setupIntent = $session->setup_intent;
        $paymentMethodId = is_object($setupIntent)
            ? ($setupIntent->payment_method ?? null)
            : null;

        if ($paymentMethodId === null) {
            return null;
        }

        return $this->enrichCardDetails([
            'provider_customer_id' => $session->customer,
            'provider_method_id' => (string) $paymentMethodId,
            'purpose' => 'trial_setup',
            'subscription_id' => $session->metadata['subscription_id'] ?? null,
            'tenant_id' => $session->metadata['tenant_id'] ?? null,
            'type' => 'card',
        ]);
    }

    /**
     * Resolve saved payment method details from a completed payment session.
     *
     * @return array<string, mixed>|null
     */
    public function resolvePaymentDetails(string $sessionId): ?array
    {
        $session = Session::retrieve($sessionId, ['expand' => ['payment_intent']]);
        $paymentIntent = $session->payment_intent;
        $paymentMethodId = is_object($paymentIntent)
            ? ($paymentIntent->payment_method ?? null)
            : null;

        if ($paymentMethodId === null) {
            return null;
        }

        return $this->enrichCardDetails([
            'provider_customer_id' => $session->customer,
            'provider_method_id' => (string) $paymentMethodId,
            'purpose' => $session->metadata['purpose'] ?? 'payment',
            'subscription_id' => $session->metadata['subscription_id'] ?? null,
            'tenant_id' => $session->metadata['tenant_id'] ?? null,
            'invoice_id' => $session->metadata['invoice_id'] ?? null,
            'type' => 'card',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function enrichCardDetails(array $data): array
    {
        $paymentMethodId = $data['provider_method_id'] ?? null;

        if (! is_string($paymentMethodId) || ! str_starts_with($paymentMethodId, 'pm_')) {
            return $data;
        }

        try {
            $method = PaymentMethod::retrieve($paymentMethodId);
        } catch (ApiErrorException) {
            return $data;
        }

        if (($method->type ?? null) !== 'card' || $method->card === null) {
            return $data;
        }

        $data['last4'] = $method->card->last4 ?? null;
        $data['brand'] = $method->card->brand ?? null;
        $data['exp_month'] = $method->card->exp_month ?? null;
        $data['exp_year'] = $method->card->exp_year ?? null;
        $data['billing_details'] = array_filter([
            'name' => $method->billing_details->name ?? null,
            'email' => $method->billing_details->email ?? null,
        ]);

        return $data;
    }
}
