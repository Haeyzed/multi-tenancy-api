<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\TenantStatus;
use App\Events\Central\TenantOnboarded;
use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use Illuminate\Support\Str;

/**
 * Tenant onboarding: provision tenant, domain, and initial subscription.
 */
class TenantOnboardingService
{
    public function __construct(
        private readonly SubscriptionLifecycleService $subscriptions,
    ) {}

    /**
     * Onboard a new tenant with domain and subscription.
     *
     * @param  array<string, mixed>  $data  Validated onboarding payload.
     */
    public function onboard(array $data): Tenant
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $domain = $data['domain'];
        $plan = Plan::query()->findOrFail($data['plan_id']);
        $billingCycle = BillingCycle::from($data['billing_cycle']);

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
            'meta' => $data['meta'] ?? [],
        ]);

        $tenant->createDomain([
            'domain' => $domain,
            'is_primary' => true,
            'is_fallback' => false,
            'verified' => false,
        ]);

        $paymentProvider = isset($data['payment_provider'])
            ? PaymentProvider::from($data['payment_provider'])
            : null;

        $this->subscriptions->subscribe(
            $tenant,
            $plan,
            $billingCycle,
            EventTriggeredBy::Admin,
            $paymentProvider,
        );

        $tenant = $tenant->fresh(['plan', 'domains', 'activeSubscription']);

        event(new TenantOnboarded($tenant));

        return $tenant;
    }
}
