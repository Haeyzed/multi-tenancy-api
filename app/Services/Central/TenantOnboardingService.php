<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\TenantStatus;
use App\Events\Central\Broadcasting\CentralTenantRegisteredBroadcast;
use App\Events\Central\TenantOnboarded;
use App\Models\Central\Plan;
use App\Models\Central\Tenant;
use App\Support\SafeBroadcast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Tenant onboarding: provision tenant, domain, and initial subscription.
 */
class TenantOnboardingService
{
    public function __construct(
        private readonly SubscriptionLifecycleService $subscriptions,
    )
    {
    }

    /**
     * Onboard a new tenant with domain and subscription.
     *
     * @param array<string, mixed> $data Validated onboarding payload.
     */
    public function onboard(array $data): Tenant
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);
        $domain = $data['domain'];
        $plan = Plan::query()->findOrFail($data['plan_id']);
        $billingCycle = BillingCycle::from($data['billing_cycle']);
        $ownerFirstName = trim((string) $data['owner_first_name']);
        $ownerLastName = trim((string) $data['owner_last_name']);
        $ownerName = trim($ownerFirstName . ' ' . $ownerLastName);
        $meta = $data['meta'] ?? [];
        $meta['owner_first_name'] = $ownerFirstName;
        $meta['owner_last_name'] = $ownerLastName;

        if (! empty($data['owner_password'])) {
            $meta['pending_owner_password_hash'] = Hash::make($data['owner_password']);
        }

        $tenant = Tenant::query()->create([
            'name' => $data['name'],
            'slug' => $slug,
            'database' => $data['database'] ?? 'tenant_' . $slug,
            'domain' => $domain,
            'status' => TenantStatus::Pending,
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'owner_email' => $data['owner_email'],
            'owner_name' => $ownerName,
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

        event(new TenantOnboarded(
            $tenant,
            $data['owner_password'] ?? null,
        ));

        return $tenant;
    }
}
