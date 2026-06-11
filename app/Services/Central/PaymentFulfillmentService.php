<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\InvoiceStatus;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\SubscriptionEventType;
use App\Enums\Central\SubscriptionStatus;
use App\Enums\Central\TenantStatus;
use App\Events\Central\SubscriptionCreated;
use App\Events\Central\SubscriptionPaymentCompleted;
use App\Events\Central\TenantOnboarded;
use App\Models\Central\Invoice;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionEvent;
use App\Models\Central\Tenant;
use App\Support\OnboardingNotes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fulfill invoices after successful payment provider confirmation.
 */
class PaymentFulfillmentService
{
    public function __construct(
        private readonly SubscriptionLifecycleService $lifecycle,
        private readonly PaymentRecordingService      $payments,
    )
    {
    }

    /**
     * Activate a subscription after initial or trial-conversion payment.
     *
     * @param array<string, mixed>|null $cardMetadata
     */
    public function fulfill(
        Invoice         $invoice,
        string          $providerPaymentId,
        PaymentProvider $provider,
        bool            $trialConversion = false,
        ?array          $cardMetadata = null,
    ): Subscription
    {
        return DB::transaction(function () use ($invoice, $providerPaymentId, $provider, $trialConversion, $cardMetadata) {
            $invoice->refresh();

            if ($invoice->status === InvoiceStatus::Paid) {
                return $invoice->subscription()->firstOrFail();
            }

            $subscription = $invoice->subscription()->with(['tenant', 'plan'])->firstOrFail();
            $tenant = $subscription->tenant;
            $plan = $subscription->plan;
            $wasPending = $tenant?->status === TenantStatus::Pending
                || $subscription->status === SubscriptionStatus::Paused;

            $this->recordPayment($invoice, $providerPaymentId, $provider, $cardMetadata);
            $this->markInvoicePaid($invoice, $providerPaymentId, $provider);

            $subscriptionStatus = SubscriptionStatus::Active;

            if (!$trialConversion && $subscription->trial_ends_at !== null && $subscription->trial_ends_at->isFuture()) {
                $subscriptionStatus = SubscriptionStatus::Trialing;
            }

            $subscription->update([
                'status' => $subscriptionStatus,
                'payment_provider' => $provider,
                'payment_provider_id' => $subscription->payment_provider_id ?? $providerPaymentId,
                'trial_ends_at' => $trialConversion ? null : $subscription->trial_ends_at,
            ]);

            $tenant?->update([
                'status' => TenantStatus::Active,
                'subscribed_at' => $tenant->subscribed_at ?? now(),
                'trial_ends_at' => $trialConversion ? null : $tenant->trial_ends_at,
            ]);

            $invoice->refresh();

            $this->recordPaymentEvent(
                $subscription,
                $plan?->id,
                $providerPaymentId,
                $invoice->id,
                $invoice->notes,
            );

            $subscription = $subscription->fresh(['tenant', 'plan', 'latestInvoice', 'lifecycleEvents']);

            event(new SubscriptionPaymentCompleted($subscription, $invoice));

            if ($wasPending) {
                event(new SubscriptionCreated($subscription));

                if ($tenant !== null) {
                    event(new TenantOnboarded($tenant->fresh(['plan', 'domains', 'activeSubscription'])));
                }
            }

            return $subscription;
        });
    }

    /**
     * @param array<string, mixed>|null $cardMetadata
     */
    private function recordPayment(
        Invoice         $invoice,
        string          $providerPaymentId,
        PaymentProvider $provider,
        ?array          $cardMetadata = null,
    ): void
    {
        $tenant = $invoice->tenant ?? Tenant::query()->findOrFail($invoice->tenant_id);

        $this->payments->recordSucceeded(
            $tenant,
            $provider,
            $providerPaymentId,
            $invoice->amount_due,
            $invoice->currency,
            $invoice,
            $cardMetadata,
        );
    }

    private function markInvoicePaid(
        Invoice         $invoice,
        string          $providerPaymentId,
        PaymentProvider $provider,
    ): void
    {
        $invoice->update([
            'status' => InvoiceStatus::Paid,
            'amount_paid' => $invoice->amount_due,
            'amount_remaining' => 0,
            'paid_at' => now(),
            'payment_intent_id' => $providerPaymentId,
            'notes' => OnboardingNotes::compose(
                $invoice->notes,
                OnboardingNotes::invoicePaid($provider->value, $providerPaymentId),
            ),
        ]);
    }

    private function recordPaymentEvent(
        Subscription $subscription,
        ?string      $planId,
        string       $providerPaymentId,
        string       $invoiceId,
        ?string      $note = null,
    ): void
    {
        SubscriptionEvent::query()->create([
            'subscription_id' => $subscription->id,
            'event_type' => SubscriptionEventType::PaymentSucceeded,
            'from_plan_id' => $planId,
            'to_plan_id' => $planId,
            'triggered_by' => EventTriggeredBy::Payment,
            'metadata' => array_filter([
                'invoice_id' => $invoiceId,
                'provider_payment_id' => $providerPaymentId,
                'note' => $note,
            ], fn($value) => $value !== null && $value !== ''),
        ]);
    }

    /**
     * Fulfill a renewal invoice and advance the billing period.
     */
    public function fulfillRenewal(
        Invoice         $invoice,
        string          $providerPaymentId,
        PaymentProvider $provider,
        Carbon          $periodStart,
        Carbon          $periodEnd,
    ): Subscription
    {
        return DB::transaction(function () use ($invoice, $providerPaymentId, $provider, $periodStart, $periodEnd) {
            $invoice->refresh();

            if ($invoice->status !== InvoiceStatus::Paid) {
                $this->recordPayment($invoice, $providerPaymentId, $provider, null);
                $this->markInvoicePaid($invoice, $providerPaymentId, $provider);
            }

            $subscription = $invoice->subscription()->with(['tenant', 'plan'])->firstOrFail();

            $invoice->refresh();

            $this->recordPaymentEvent(
                $subscription,
                $subscription->plan_id,
                $providerPaymentId,
                $invoice->id,
                $invoice->notes,
            );

            return $this->lifecycle->advanceBillingPeriod(
                $subscription,
                $periodStart,
                $periodEnd,
                EventTriggeredBy::Payment,
            );
        });
    }
}
