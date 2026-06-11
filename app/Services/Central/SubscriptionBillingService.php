<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\SubscriptionEventType;
use App\Enums\Central\SubscriptionStatus;
use App\Enums\Central\TenantStatus;
use App\Events\Central\SubscriptionPaymentFailed;
use App\Events\Central\TrialEndingSoon;
use App\Models\Central\Invoice;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionEvent;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Automated renewal, trial expiration, and off-session billing.
 */
class SubscriptionBillingService
{
    public function __construct(
        private readonly SubscriptionLifecycleService $lifecycle,
        private readonly PaymentFulfillmentService    $fulfillment,
        private readonly PaymentGatewayManager        $gateways,
        private readonly SelfOnboardingService        $selfOnboarding,
    )
    {
    }

    /**
     * Process all subscriptions due for renewal.
     */
    public function processDueRenewals(): int
    {
        $count = 0;

        foreach ($this->renewalsDue() as $subscription) {
            try {
                $this->processRenewal($subscription);
                $count++;
            } catch (Throwable $exception) {
                Log::error('Renewal processing failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * @return Collection<int, Subscription>
     */
    private function renewalsDue(): Collection
    {
        return Subscription::query()
            ->with(['tenant', 'plan'])
            ->where('status', SubscriptionStatus::Active)
            ->where('current_period_end', '<=', now())
            ->get();
    }

    /**
     * Attempt renewal billing for a single subscription.
     */
    public function processRenewal(Subscription $subscription): void
    {
        $subscription->loadMissing(['tenant', 'plan']);

        if ($subscription->tenant === null || $subscription->plan === null) {
            return;
        }

        $now = now();
        $periodEnd = $this->lifecycle->calculatePeriodEnd($now, $subscription->billing_cycle);
        $invoice = $this->lifecycle->issueBillingInvoice($subscription, $now, $periodEnd);

        $this->attemptCollection($subscription, $invoice, isRenewal: true, periodStart: $now, periodEnd: $periodEnd);
    }

    private function attemptCollection(
        Subscription $subscription,
        Invoice      $invoice,
        bool         $isRenewal,
        Carbon       $periodStart,
        Carbon       $periodEnd,
        bool         $trialConversion = false,
    ): void
    {
        $tenant = $subscription->tenant;
        $provider = $subscription->payment_provider;

        if ($tenant === null) {
            return;
        }

        if ($subscription->payment_method_id !== null) {
            try {
                $gateway = $this->gateways->recurring($provider);
                $charge = $gateway->chargeSavedMethod($invoice, $tenant, $subscription);

                if ($charge->success && $charge->reference !== null) {
                    if ($isRenewal) {
                        $this->fulfillment->fulfillRenewal($invoice, $charge->reference, $provider, $periodStart, $periodEnd);
                    } else {
                        $this->fulfillment->fulfill($invoice, $charge->reference, $provider, trialConversion: $trialConversion);
                    }

                    return;
                }

                $this->handleCollectionFailure($subscription, $invoice, $charge->failureMessage ?? 'Charge failed.');
            } catch (Throwable $exception) {
                $this->handleCollectionFailure($subscription, $invoice, $exception->getMessage());
            }

            return;
        }

        $this->handleCollectionFailure($subscription, $invoice, 'No saved payment method on file.');
    }

    private function handleCollectionFailure(
        Subscription $subscription,
        Invoice      $invoice,
        string       $reason,
    ): void
    {
        $subscription->update(['status' => SubscriptionStatus::PastDue]);
        $subscription->tenant?->update(['status' => TenantStatus::Suspended]);

        SubscriptionEvent::query()->create([
            'subscription_id' => $subscription->id,
            'event_type' => SubscriptionEventType::PaymentFailed,
            'from_plan_id' => $subscription->plan_id,
            'to_plan_id' => $subscription->plan_id,
            'triggered_by' => EventTriggeredBy::System,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'reason' => $reason,
            ],
        ]);

        $checkoutUrl = null;

        try {
            $checkout = $this->selfOnboarding->checkout(
                $subscription->tenant,
                $subscription->payment_provider,
            );
            $checkoutUrl = $checkout['checkout_url'];
        } catch (Throwable) {
            // Checkout retry unavailable; notification will omit URL.
        }

        event(new SubscriptionPaymentFailed($subscription->fresh(['tenant', 'plan']), $invoice, $reason, $checkoutUrl));
    }

    /**
     * Process trialing subscriptions whose trial has ended.
     */
    public function processExpiredTrials(): int
    {
        $count = 0;

        foreach ($this->expiredTrials() as $subscription) {
            try {
                $this->processTrialExpiration($subscription);
                $count++;
            } catch (Throwable $exception) {
                Log::error('Trial expiration processing failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * @return Collection<int, Subscription>
     */
    private function expiredTrials(): Collection
    {
        return Subscription::query()
            ->with(['tenant', 'plan'])
            ->where('status', SubscriptionStatus::Trialing)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();
    }

    /**
     * Bill a subscription when its trial period ends.
     */
    public function processTrialExpiration(Subscription $subscription): void
    {
        $subscription->loadMissing(['tenant', 'plan']);

        if ($subscription->tenant === null || $subscription->plan === null) {
            return;
        }

        $now = now();
        $periodEnd = $this->lifecycle->calculatePeriodEnd($now, $subscription->billing_cycle);
        $invoice = $this->lifecycle->issueBillingInvoice($subscription, $now, $periodEnd);

        $this->attemptCollection($subscription, $invoice, isRenewal: false, periodStart: $now, periodEnd: $periodEnd, trialConversion: true);
    }

    /**
     * Send reminders for trials ending soon.
     */
    public function sendTrialEndingReminders(): int
    {
        $count = 0;
        $days = (int)config('payments.trial_reminder_days', 3);
        $targetDate = now()->addDays($days)->toDateString();

        $subscriptions = Subscription::query()
            ->with(['tenant', 'plan'])
            ->where('status', SubscriptionStatus::Trialing)
            ->whereDate('trial_ends_at', $targetDate)
            ->get();

        foreach ($subscriptions as $subscription) {
            event(new TrialEndingSoon($subscription, $days));
            $count++;
        }

        return $count;
    }
}
