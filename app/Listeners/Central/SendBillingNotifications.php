<?php

declare(strict_types=1);

namespace App\Listeners\Central;

use App\Events\Central\PaymentMethodSaved;
use App\Events\Central\SubscriptionPaymentCompleted;
use App\Events\Central\SubscriptionPaymentFailed;
use App\Events\Central\TenantOnboarded;
use App\Events\Central\TrialEndingSoon;
use App\Models\Central\Tenant;
use App\Services\Central\CentralNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Send billing-related notifications using central templates.
 */
class SendBillingNotifications implements ShouldQueue
{
    public function __construct(
        private readonly CentralNotificationService $notifications,
    ) {}

    public function handleTenantOnboarded(TenantOnboarded $event): void
    {
        $tenant = $event->tenant->loadMissing('plan');

        $this->notifications->sendFromTemplate(
            'welcome_signup',
            $this->tenantVariables($tenant),
            mailRecipients: [$tenant->owner_email],
            notificationType: 'tenant.onboarded',
        );

        $this->notifications->sendFromTemplate(
            'billing_alert_admin',
            [
                'event_title' => 'New tenant onboarded',
                'event_body' => "{$tenant->name} completed signup on the ".($tenant->plan?->name ?? 'selected').' plan.',
                'tenant_name' => $tenant->name,
                'tenant_id' => $tenant->id,
            ],
            inAppUsers: $this->notifications->billingAlertRecipients()->all(),
            notificationType: 'tenant.onboarded',
        );
    }

    public function handleSubscriptionPaymentCompleted(SubscriptionPaymentCompleted $event): void
    {
        $subscription = $event->subscription->loadMissing(['tenant', 'plan']);
        $tenant = $subscription->tenant;
        $invoice = $event->invoice;

        if ($tenant === null) {
            return;
        }

        $variables = array_merge($this->tenantVariables($tenant), [
            'amount' => number_format($invoice->amount_due / 100, 2),
            'currency' => $invoice->currency,
            'invoice_number' => $invoice->invoice_number,
        ]);

        $this->notifications->sendFromTemplate(
            'payment_confirmed',
            $variables,
            mailRecipients: [$tenant->owner_email],
            notificationType: 'payment.confirmed',
        );

        $this->notifications->sendFromTemplate(
            'billing_alert_admin',
            [
                'event_title' => 'Payment confirmed',
                'event_body' => "{$tenant->name} paid invoice {$invoice->invoice_number}.",
                'tenant_name' => $tenant->name,
                'tenant_id' => $tenant->id,
            ],
            inAppUsers: $this->notifications->billingAlertRecipients()->all(),
            notificationType: 'payment.confirmed',
        );
    }

    public function handleSubscriptionPaymentFailed(SubscriptionPaymentFailed $event): void
    {
        $subscription = $event->subscription->loadMissing('tenant');
        $tenant = $subscription->tenant;
        $invoice = $event->invoice;

        if ($tenant === null) {
            return;
        }

        $variables = array_merge($this->tenantVariables($tenant), [
            'amount' => number_format($invoice->amount_due / 100, 2),
            'currency' => $invoice->currency,
            'reason' => $event->reason,
            'checkout_url' => $event->checkoutUrl ?? '',
        ]);

        $this->notifications->sendFromTemplate(
            'payment_failed',
            $variables,
            mailRecipients: [$tenant->owner_email],
            notificationType: 'payment.failed',
        );

        $this->notifications->sendFromTemplate(
            'billing_alert_admin',
            [
                'event_title' => 'Payment failed',
                'event_body' => "{$tenant->name} payment failed: {$event->reason}",
                'tenant_name' => $tenant->name,
                'tenant_id' => $tenant->id,
            ],
            inAppUsers: $this->notifications->billingAlertRecipients()->all(),
            notificationType: 'payment.failed',
        );
    }

    public function handleTrialEndingSoon(TrialEndingSoon $event): void
    {
        $subscription = $event->subscription->loadMissing('tenant');
        $tenant = $subscription->tenant;

        if ($tenant === null) {
            return;
        }

        $variables = array_merge($this->tenantVariables($tenant), [
            'days_remaining' => (string) $event->daysRemaining,
            'trial_ends_at' => $subscription->trial_ends_at?->toFormattedDateString() ?? 'soon',
        ]);

        $this->notifications->sendFromTemplate(
            'trial_ending',
            $variables,
            mailRecipients: [$tenant->owner_email],
            notificationType: 'trial.ending',
        );
    }

    public function handlePaymentMethodSaved(PaymentMethodSaved $event): void
    {
        $subscription = $event->subscription->loadMissing('tenant');
        $tenant = $subscription->tenant;

        if ($tenant === null) {
            return;
        }

        $this->notifications->sendFromTemplate(
            'payment_method_saved',
            $this->tenantVariables($tenant),
            mailRecipients: [$tenant->owner_email],
            notificationType: 'payment_method.saved',
        );
    }

    /**
     * @return array<string, scalar|null>
     */
    private function tenantVariables(Tenant $tenant): array
    {
        return [
            'owner_name' => $tenant->owner_name,
            'tenant_name' => $tenant->name,
            'plan_name' => $tenant->plan?->name ?? 'your plan',
            'domain' => $tenant->domain,
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
        ];
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
