<?php

declare(strict_types=1);

namespace App\Listeners\Central;

use App\Events\Central\SubscriptionCancelled;
use App\Events\Central\SubscriptionCreated;
use App\Events\Central\SubscriptionPaymentCompleted;
use App\Events\Central\SubscriptionPlanChanged;
use App\Events\Central\SubscriptionRenewed;
use App\Events\Central\TenantOnboarded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Log central subscription and tenant lifecycle events.
 */
class LogCentralLifecycleEvents implements ShouldQueue
{
    public function handleSubscriptionCreated(SubscriptionCreated $event): void
    {
        Log::info('Subscription created', [
            'subscription_id' => $event->subscription->id,
            'tenant_id' => $event->subscription->tenant_id,
            'plan_id' => $event->subscription->plan_id,
        ]);
    }

    public function handleSubscriptionCancelled(SubscriptionCancelled $event): void
    {
        Log::info('Subscription cancelled', [
            'subscription_id' => $event->subscription->id,
            'tenant_id' => $event->subscription->tenant_id,
        ]);
    }

    public function handleSubscriptionPlanChanged(SubscriptionPlanChanged $event): void
    {
        Log::info('Subscription plan changed', [
            'subscription_id' => $event->subscription->id,
            'change_type' => $event->changeType->value,
            'plan_id' => $event->subscription->plan_id,
        ]);
    }

    public function handleSubscriptionRenewed(SubscriptionRenewed $event): void
    {
        Log::info('Subscription renewed', [
            'subscription_id' => $event->subscription->id,
            'tenant_id' => $event->subscription->tenant_id,
        ]);
    }

    public function handleSubscriptionPaymentCompleted(SubscriptionPaymentCompleted $event): void
    {
        Log::info('Subscription payment completed', [
            'subscription_id' => $event->subscription->id,
            'invoice_id' => $event->invoice->id,
            'tenant_id' => $event->subscription->tenant_id,
        ]);
    }

    public function handleTenantOnboarded(TenantOnboarded $event): void
    {
        Log::info('Tenant onboarded', [
            'tenant_id' => $event->tenant->id,
            'slug' => $event->tenant->slug,
            'plan_id' => $event->tenant->plan_id,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            SubscriptionCreated::class => 'handleSubscriptionCreated',
            SubscriptionCancelled::class => 'handleSubscriptionCancelled',
            SubscriptionPlanChanged::class => 'handleSubscriptionPlanChanged',
            SubscriptionRenewed::class => 'handleSubscriptionRenewed',
            SubscriptionPaymentCompleted::class => 'handleSubscriptionPaymentCompleted',
            TenantOnboarded::class => 'handleTenantOnboarded',
        ];
    }
}
