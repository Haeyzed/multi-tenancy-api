<?php

declare(strict_types=1);

namespace App\Listeners\Central;

use App\Events\Central\PaymentMethodSaved;
use App\Events\Central\SubscriptionPaymentCompleted;
use App\Events\Central\SubscriptionPaymentFailed;
use App\Events\Central\TenantOnboarded;
use App\Events\Central\TrialEndingSoon;
use App\Notifications\Central\PaymentConfirmedNotification;
use App\Notifications\Central\PaymentFailedNotification;
use App\Notifications\Central\PaymentMethodSavedNotification;
use App\Notifications\Central\TrialEndingNotification;
use App\Notifications\Central\WelcomeSignupNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Send billing-related email notifications.
 */
class SendBillingNotifications implements ShouldQueue
{
    public function handleTenantOnboarded(TenantOnboarded $event): void
    {
        $tenant = $event->tenant->fresh(['plan', 'domains']);

        if ($tenant === null) {
            return;
        }

        Notification::route('mail', $tenant->owner_email)
            ->notify(new WelcomeSignupNotification($tenant));
    }

    public function handleSubscriptionPaymentCompleted(SubscriptionPaymentCompleted $event): void
    {
        $tenant = $event->subscription->tenant;

        if ($tenant === null) {
            return;
        }

        Notification::route('mail', $tenant->owner_email)
            ->notify(new PaymentConfirmedNotification($tenant, $event->invoice));
    }

    public function handleSubscriptionPaymentFailed(SubscriptionPaymentFailed $event): void
    {
        $tenant = $event->subscription->tenant;

        if ($tenant === null) {
            return;
        }

        Notification::route('mail', $tenant->owner_email)
            ->notify(new PaymentFailedNotification(
                $tenant,
                $event->invoice,
                $event->reason,
                $event->checkoutUrl,
            ));
    }

    public function handleTrialEndingSoon(TrialEndingSoon $event): void
    {
        $tenant = $event->subscription->tenant;

        if ($tenant === null) {
            return;
        }

        Notification::route('mail', $tenant->owner_email)
            ->notify(new TrialEndingNotification($tenant, $event->subscription, $event->daysRemaining));
    }

    public function handlePaymentMethodSaved(PaymentMethodSaved $event): void
    {
        $tenant = $event->subscription->tenant;

        if ($tenant === null) {
            return;
        }

        Notification::route('mail', $tenant->owner_email)
            ->notify(new PaymentMethodSavedNotification($tenant));
    }

    /**
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            TenantOnboarded::class => 'handleTenantOnboarded',
            SubscriptionPaymentCompleted::class => 'handleSubscriptionPaymentCompleted',
            SubscriptionPaymentFailed::class => 'handleSubscriptionPaymentFailed',
            TrialEndingSoon::class => 'handleTrialEndingSoon',
            PaymentMethodSaved::class => 'handlePaymentMethodSaved',
        ];
    }
}
