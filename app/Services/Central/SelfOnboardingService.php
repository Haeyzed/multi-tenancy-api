<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Events\Central\Broadcasting\CentralTenantRegisteredBroadcast;
use App\Support\OnboardingNotes;
use App\Support\SafeBroadcast;
use App\Enums\Central\BillingCycle;
use App\Enums\Central\InvoiceStatus;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\PaymentStatus;
use App\Enums\Central\SubscriptionStatus;
use App\Enums\Central\TenantStatus;
use App\Models\Central\Invoice;
use App\Models\Central\Payment;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Self-service tenant onboarding with optional payment checkout.
 */
class SelfOnboardingService
{
    public function __construct(
        private readonly SubscriptionLifecycleService $subscriptions,
        private readonly PaymentGatewayManager $gateways,
    ) {}

    /**
     * Register a new tenant and initiate subscription billing.
     *
     * @param  array<string, mixed>  $data
     * @return array{
     *     tenant: Tenant,
     *     requires_payment: bool,
     *     requires_payment_method: bool,
     *     checkout_url: string|null,
     *     payment_provider: string|null,
     *     invoice_id: string|null
     * }
     */
    public function onboard(array $data): array
    {
        set_time_limit((int) config('tenancy.self_onboarding_max_execution_time', 600));

        // Tenant creation must not run inside DB::transaction — Stancl's TenantCreated
        // pipeline (CreateDatabase, MigrateDatabase) runs synchronously and breaks it.
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $domain = $data['domain'];
        $plan = Plan::query()->findOrFail($data['plan_id']);
        $billingCycle = BillingCycle::from($data['billing_cycle']);
        $paymentProvider = PaymentProvider::from($data['payment_provider']);

        $meta = $data['meta'] ?? [];
        $userNotes = isset($data['notes']) ? trim((string) $data['notes']) : '';
        $onboardingNotes = OnboardingNotes::compose(
            $userNotes !== '' ? $userNotes : null,
            OnboardingNotes::signupStarted(
                $plan->name,
                $billingCycle->value,
                $paymentProvider->value,
            ),
        );
        $meta['onboarding_notes'] = $onboardingNotes;

        if (! empty($data['owner_password'])) {
            $meta['pending_owner_password'] = Crypt::encryptString($data['owner_password']);
        }

        $tenant = Tenant::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'database' => $data['database'] ?? 'tenant_'.$slug,
            'domain' => $domain,
            'status' => TenantStatus::Pending,
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'owner_email' => $data['owner_email'],
            'owner_name' => $data['owner_name'],
            'settings' => $data['settings'] ?? [],
            'meta' => $meta,
        ]);

        $tenant->createDomain([
            'domain' => $domain,
            'is_primary' => true,
            'is_fallback' => false,
            'verified' => false,
        ]);

        SafeBroadcast::dispatch(new CentralTenantRegisteredBroadcast(
            $tenant->fresh(['plan', 'domains', 'activeSubscription']),
        ));

        $initiated = $this->subscriptions->initiateForSignup(
            $tenant,
            $plan,
            $billingCycle,
            $paymentProvider,
            $onboardingNotes,
        );

        $result = [
            'tenant' => $tenant->fresh(['plan', 'domains', 'activeSubscription']),
            'plan' => $plan,
            'billing_cycle' => $billingCycle,
            'payment_provider' => $paymentProvider,
            'requires_payment' => $initiated['requires_payment'],
            'invoice' => $initiated['invoice'],
            'subscription' => $initiated['subscription'],
        ];

        $checkoutUrl = null;
        $invoiceId = null;
        $requiresPaymentMethod = false;

        if ($result['requires_payment'] && $result['invoice'] !== null) {
            $checkout = $this->createCheckout(
                $result['invoice'],
                $result['tenant'],
                $result['plan'],
                $result['billing_cycle'],
                $result['payment_provider'],
                $data['success_url'] ?? null,
                $data['cancel_url'] ?? null,
            );

            $checkoutUrl = $checkout['checkout_url'];
            $invoiceId = $result['invoice']->id;
        } elseif (! $result['requires_payment']) {
            $requiresPaymentMethod = true;
            $checkout = $this->createSetupCheckout(
                $result['tenant'],
                $result['subscription'],
                $result['payment_provider'],
                $data['success_url'] ?? null,
                $data['cancel_url'] ?? null,
            );
            $checkoutUrl = $checkout['checkout_url'];
        }

        return [
            'tenant' => $result['tenant']->fresh(['plan', 'domains', 'activeSubscription']),
            'requires_payment' => $result['requires_payment'],
            'requires_payment_method' => $requiresPaymentMethod,
            'checkout_url' => $checkoutUrl,
            'payment_provider' => $result['payment_provider']->value,
            'invoice_id' => $invoiceId,
        ];
    }

    /**
     * Create a checkout session for an existing unpaid onboarding invoice.
     *
     * @return array{checkout_url: string, invoice_id: string}
     */
    public function checkout(
        Tenant $tenant,
        PaymentProvider $paymentProvider,
        ?string $successUrl = null,
        ?string $cancelUrl = null,
    ): array {
        $tenant->loadMissing(['plan', 'subscriptions']);

        $subscription = $tenant->subscriptions()->latest('created_at')->first();

        if ($subscription === null) {
            throw new RuntimeException('No subscription found for this tenant.');
        }

        if ($tenant->status === TenantStatus::Active && $subscription->status !== SubscriptionStatus::PastDue) {
            throw new RuntimeException('Tenant is already active.');
        }

        $invoice = Invoice::query()
            ->whereKey($subscription->latest_invoice_id)
            ->where('status', InvoiceStatus::Open)
            ->first();

        if ($invoice === null) {
            throw new RuntimeException('No open invoice found for this tenant.');
        }
        $plan = $tenant->plan ?? Plan::query()->findOrFail($subscription->plan_id);

        return $this->createCheckout(
            $invoice,
            $tenant,
            $plan,
            $subscription->billing_cycle,
            $paymentProvider,
            $successUrl,
            $cancelUrl,
        );
    }

    /**
     * @return array{checkout_url: string, invoice_id: string}
     */
    private function createCheckout(
        Invoice $invoice,
        Tenant $tenant,
        Plan $plan,
        BillingCycle $billingCycle,
        PaymentProvider $paymentProvider,
        ?string $successUrl,
        ?string $cancelUrl,
    ): array {
        $gateway = $this->gateways->gateway($paymentProvider);

        $successUrl ??= (string) config('payments.checkout.success_url');
        $cancelUrl ??= (string) config('payments.checkout.cancel_url');

        $session = $gateway->createCheckout(
            $invoice,
            $tenant,
            $plan,
            $billingCycle,
            $successUrl,
            $cancelUrl,
        );

        $invoice->update(['payment_intent_id' => $session->reference]);

        Payment::query()->updateOrCreate(
            [
                'invoice_id' => $invoice->id,
                'provider_payment_id' => $session->reference,
            ],
            [
                'tenant_id' => $tenant->id,
                'amount' => $invoice->amount_due,
                'currency' => $invoice->currency,
                'status' => PaymentStatus::Pending,
                'payment_provider' => $paymentProvider,
            ],
        );

        return [
            'checkout_url' => $session->checkoutUrl,
            'invoice_id' => $invoice->id,
        ];
    }

    /**
     * @return array{checkout_url: string}
     */
    private function createSetupCheckout(
        Tenant $tenant,
        Subscription $subscription,
        PaymentProvider $paymentProvider,
        ?string $successUrl,
        ?string $cancelUrl,
    ): array {
        $gateway = $this->gateways->recurring($paymentProvider);

        $successUrl ??= (string) config('payments.checkout.success_url');
        $cancelUrl ??= (string) config('payments.checkout.cancel_url');

        $session = $gateway->createSetupCheckout(
            $tenant,
            $subscription,
            $successUrl,
            $cancelUrl,
        );

        return ['checkout_url' => $session->checkoutUrl];
    }
}
