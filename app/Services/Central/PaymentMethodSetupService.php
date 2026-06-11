<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\PaymentProvider;
use App\Enums\Central\SubscriptionStatus;
use App\Enums\Central\TenantStatus;
use App\Events\Central\PaymentMethodSaved;
use App\Events\Central\SubscriptionCreated;
use App\Events\Central\TenantOnboarded;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Services\Payment\PaystackGateway;
use App\Services\Payment\StripeGateway;
use App\Support\OnboardingNotes;

/**
 * Handle payment method setup webhooks during trial signup.
 */
class PaymentMethodSetupService
{
    public function __construct(
        private readonly PaymentMethodStorageService $storage,
        private readonly StripeGateway $stripe,
        private readonly PaystackGateway $paystack,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleStripeSetup(array $payload): void
    {
        $sessionId = $payload['data']['object']['id'] ?? null;

        if ($sessionId === null) {
            return;
        }

        $details = $this->stripe->resolveSetupDetails((string) $sessionId);

        if ($details === null) {
            return;
        }

        $this->persistFromDetails($details, PaymentProvider::Stripe);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handlePaystackSetup(array $payload): void
    {
        $details = $this->paystack->extractPaymentMethodFromWebhook($payload);

        if ($details === null) {
            return;
        }

        $this->persistFromDetails($details, PaymentProvider::Paystack);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public function persistDetails(array $details, PaymentProvider $provider): void
    {
        $this->persistFromDetails($details, $provider);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function persistFromDetails(array $details, PaymentProvider $provider): void
    {
        $tenantId = $details['tenant_id'] ?? null;
        $subscriptionId = $details['subscription_id'] ?? null;

        if ($tenantId === null || $subscriptionId === null || empty($details['provider_method_id'])) {
            return;
        }

        $tenant = Tenant::query()->find($tenantId);
        $subscription = Subscription::query()->find($subscriptionId);

        if ($tenant === null || $subscription === null) {
            return;
        }

        $this->storage->store($tenant, $subscription, $provider, $details);

        $wasPending = $tenant->status === TenantStatus::Pending;

        $subscriptionStatus = $subscription->trial_ends_at !== null && $subscription->trial_ends_at->isFuture()
            ? SubscriptionStatus::Trialing
            : SubscriptionStatus::Active;

        $meta = $tenant->meta ?? [];
        $meta['onboarding_notes'] = OnboardingNotes::compose(
            $meta['onboarding_notes'] ?? null,
            OnboardingNotes::cardVerified(
                $provider->value,
                isset($details['provider_method_id']) ? (string) $details['provider_method_id'] : null,
            ),
        );

        $tenant->update([
            'status' => TenantStatus::Active,
            'subscribed_at' => $tenant->subscribed_at ?? now(),
            'meta' => $meta,
        ]);

        $subscription->update([
            'status' => $subscriptionStatus,
            'payment_provider' => $provider,
        ]);

        $tenant = $tenant->fresh(['plan', 'domains', 'activeSubscription']);
        $subscription = $subscription->fresh(['tenant', 'plan', 'latestInvoice']);

        event(new PaymentMethodSaved($subscription));

        if ($wasPending) {
            event(new SubscriptionCreated($subscription));
        }

        $meta = $tenant->meta ?? [];

        if (empty($meta['owner_user_id'])) {
            event(new TenantOnboarded($tenant));
        }
    }
}
