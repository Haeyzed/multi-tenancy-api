<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\EventTriggeredBy;
use App\Enums\Central\InvoiceStatus;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\SubscriptionEventType;
use App\Enums\Central\SubscriptionStatus;
use App\Enums\Central\TenantStatus;
use App\Events\Central\SubscriptionCancelled;
use App\Events\Central\SubscriptionCreated;
use App\Events\Central\SubscriptionPlanChanged;
use App\Events\Central\SubscriptionRenewed;
use App\Models\Central\Invoice;
use App\Models\Central\InvoiceItem;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\SubscriptionEvent;
use App\Models\Central\SubscriptionItem;
use App\Models\Central\Tenant;
use App\Support\OnboardingNotes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Subscription lifecycle orchestration: billing, events, and tenant sync.
 */
class SubscriptionLifecycleService
{
    /**
     * Create a new subscription for a tenant on the given plan.
     *
     * @param  PaymentProvider|null  $paymentProvider  Optional payment provider.
     */
    public function subscribe(
        Tenant $tenant,
        Plan $plan,
        BillingCycle $billingCycle,
        EventTriggeredBy $triggeredBy = EventTriggeredBy::System,
        ?PaymentProvider $paymentProvider = null,
    ): Subscription {
        return DB::transaction(function () use ($tenant, $plan, $billingCycle, $triggeredBy, $paymentProvider) {
            $now = now();
            $trialEndsAt = $plan->trial_days > 0 ? $now->copy()->addDays($plan->trial_days) : null;
            $periodEnd = $this->periodEnd($now, $billingCycle);

            $subscription = Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $trialEndsAt ? SubscriptionStatus::Trialing : SubscriptionStatus::Active,
                'billing_cycle' => $billingCycle,
                'current_period_start' => $now,
                'current_period_end' => $periodEnd,
                'trial_ends_at' => $trialEndsAt,
                'payment_provider' => $paymentProvider ?? PaymentProvider::Stripe,
            ]);

            $this->syncSubscriptionItem($subscription, $plan, $billingCycle);
            $invoice = $this->generateInvoice($subscription, $plan, $billingCycle, $now, $periodEnd);
            $subscription->update(['latest_invoice_id' => $invoice->id]);

            $this->recordEvent($subscription, SubscriptionEventType::Created, null, $plan->id, $triggeredBy);

            $this->syncTenantFromSubscription($tenant, $subscription, $plan, $periodEnd, $trialEndsAt);

            event(new SubscriptionCreated($subscription->fresh(['tenant', 'plan', 'latestInvoice'])));

            return $subscription->fresh(['plan', 'subscriptionItems', 'latestInvoice', 'lifecycleEvents']);
        });
    }

    /**
     * Initiate a self-service subscription (pending payment or trial).
     *
     * @return array{subscription: Subscription, invoice: Invoice|null, requires_payment: bool}
     */
    public function initiateForSignup(
        Tenant $tenant,
        Plan $plan,
        BillingCycle $billingCycle,
        PaymentProvider $paymentProvider,
        ?string $onboardingNotes = null,
    ): array {
        return DB::transaction(function () use ($tenant, $plan, $billingCycle, $paymentProvider, $onboardingNotes) {
            $now = now();
            $trialEndsAt = $plan->trial_days > 0 ? $now->copy()->addDays($plan->trial_days) : null;
            $periodEnd = $this->periodEnd($now, $billingCycle);
            $requiresPayment = $plan->trial_days === 0;

            $subscription = Subscription::query()->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $requiresPayment ? SubscriptionStatus::Paused : SubscriptionStatus::Trialing,
                'billing_cycle' => $billingCycle,
                'current_period_start' => $now,
                'current_period_end' => $periodEnd,
                'trial_ends_at' => $trialEndsAt,
                'payment_provider' => $paymentProvider,
            ]);

            $this->syncSubscriptionItem($subscription, $plan, $billingCycle);

            $invoice = null;

            if ($requiresPayment) {
                $invoiceNotes = OnboardingNotes::compose(
                    $onboardingNotes,
                    OnboardingNotes::invoiceIssued($plan->name, $billingCycle->value),
                );
                $invoice = $this->generateInvoice(
                    $subscription,
                    $plan,
                    $billingCycle,
                    $now,
                    $periodEnd,
                    $invoiceNotes,
                );
                $subscription->update(['latest_invoice_id' => $invoice->id]);
            }

            $this->recordEvent(
                $subscription,
                SubscriptionEventType::Created,
                null,
                $plan->id,
                EventTriggeredBy::User,
                $onboardingNotes !== null ? ['note' => $onboardingNotes] : null,
            );

            $tenant->update([
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'status' => TenantStatus::Pending,
                'trial_ends_at' => $trialEndsAt,
                'subscribed_at' => null,
                'expires_at' => $periodEnd,
            ]);

            return [
                'subscription' => $subscription->fresh(['plan', 'subscriptionItems', 'latestInvoice', 'lifecycleEvents']),
                'invoice' => $invoice,
                'requires_payment' => $requiresPayment,
            ];
        });
    }

    /**
     * Upgrade a subscription to a higher-tier plan.
     */
    public function upgrade(
        Subscription $subscription,
        Plan $newPlan,
        EventTriggeredBy $triggeredBy = EventTriggeredBy::Admin,
    ): Subscription {
        return $this->changePlan($subscription, $newPlan, SubscriptionEventType::Upgraded, $triggeredBy);
    }

    /**
     * Downgrade a subscription to a lower-tier plan.
     */
    public function downgrade(
        Subscription $subscription,
        Plan $newPlan,
        EventTriggeredBy $triggeredBy = EventTriggeredBy::Admin,
    ): Subscription {
        return $this->changePlan($subscription, $newPlan, SubscriptionEventType::Downgraded, $triggeredBy);
    }

    /**
     * Cancel an active subscription.
     *
     * @param  string|null  $reason  Optional cancellation reason.
     */
    public function cancel(
        Subscription $subscription,
        ?string $reason = null,
        EventTriggeredBy $triggeredBy = EventTriggeredBy::Admin,
    ): Subscription {
        return DB::transaction(function () use ($subscription, $reason, $triggeredBy) {
            $fromPlanId = $subscription->plan_id;

            $subscription->update([
                'status' => SubscriptionStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            $this->recordEvent($subscription, SubscriptionEventType::Cancelled, $fromPlanId, null, $triggeredBy, [
                'reason' => $reason,
            ]);

            $subscription->tenant?->update(['status' => TenantStatus::Cancelled]);

            event(new SubscriptionCancelled($subscription->fresh(['tenant', 'plan'])));

            return $subscription->fresh(['plan', 'lifecycleEvents']);
        });
    }

    /**
     * Renew a subscription billing period and generate a new invoice.
     */
    public function renew(
        Subscription $subscription,
        EventTriggeredBy $triggeredBy = EventTriggeredBy::System,
    ): Subscription {
        return DB::transaction(function () use ($subscription, $triggeredBy) {
            $subscription->loadMissing('plan');
            $plan = $subscription->plan;

            if ($plan === null) {
                throw new InvalidArgumentException('Subscription has no plan assigned.');
            }

            $now = now();
            $periodEnd = $this->periodEnd($now, $subscription->billing_cycle);

            $subscription->update([
                'current_period_start' => $now,
                'current_period_end' => $periodEnd,
                'status' => SubscriptionStatus::Active,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);

            $invoice = $this->generateInvoice($subscription, $plan, $subscription->billing_cycle, $now, $periodEnd);
            $subscription->update(['latest_invoice_id' => $invoice->id]);

            $this->recordEvent($subscription, SubscriptionEventType::Renewed, $plan->id, $plan->id, $triggeredBy);

            $subscription->tenant?->update([
                'status' => TenantStatus::Active,
                'expires_at' => $periodEnd,
            ]);

            event(new SubscriptionRenewed($subscription->fresh(['tenant', 'plan', 'latestInvoice'])));

            return $subscription->fresh(['plan', 'latestInvoice', 'lifecycleEvents']);
        });
    }

    /**
     * Reactivate a cancelled subscription on its current plan.
     */
    public function reactivate(
        Subscription $subscription,
        EventTriggeredBy $triggeredBy = EventTriggeredBy::Admin,
    ): Subscription {
        return DB::transaction(function () use ($subscription, $triggeredBy) {
            $subscription->loadMissing('plan');
            $plan = $subscription->plan;

            if ($plan === null) {
                throw new InvalidArgumentException('Subscription has no plan assigned.');
            }

            $now = now();
            $periodEnd = $this->periodEnd($now, $subscription->billing_cycle);

            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'current_period_start' => $now,
                'current_period_end' => $periodEnd,
                'cancelled_at' => null,
                'cancellation_reason' => null,
            ]);

            $this->recordEvent($subscription, SubscriptionEventType::Reactivated, $plan->id, $plan->id, $triggeredBy);

            $this->syncTenantFromSubscription($subscription->tenant, $subscription, $plan, $periodEnd, $subscription->trial_ends_at);

            return $subscription->fresh(['plan', 'lifecycleEvents']);
        });
    }

    /**
     * Change subscription plan and record lifecycle event.
     */
    private function changePlan(
        Subscription $subscription,
        Plan $newPlan,
        SubscriptionEventType $eventType,
        EventTriggeredBy $triggeredBy,
    ): Subscription {
        return DB::transaction(function () use ($subscription, $newPlan, $eventType, $triggeredBy) {
            $fromPlanId = $subscription->plan_id;

            $subscription->update(['plan_id' => $newPlan->id]);
            $this->syncSubscriptionItem($subscription, $newPlan, $subscription->billing_cycle);

            $this->recordEvent($subscription, $eventType, $fromPlanId, $newPlan->id, $triggeredBy);

            $subscription->tenant?->update(['plan_id' => $newPlan->id]);

            event(new SubscriptionPlanChanged($subscription->fresh(['tenant', 'plan']), $eventType));

            return $subscription->fresh(['plan', 'subscriptionItems', 'lifecycleEvents']);
        });
    }

    /**
     * Sync tenant record from subscription state.
     */
    private function syncTenantFromSubscription(
        ?Tenant $tenant,
        Subscription $subscription,
        Plan $plan,
        Carbon $periodEnd,
        ?Carbon $trialEndsAt,
    ): void {
        $tenant?->update([
            'plan_id' => $plan->id,
            'billing_cycle' => $subscription->billing_cycle,
            'status' => TenantStatus::Active,
            'trial_ends_at' => $trialEndsAt,
            'subscribed_at' => $tenant->subscribed_at ?? now(),
            'expires_at' => $periodEnd,
        ]);
    }

    /**
     * Create or refresh the primary subscription line item.
     */
    private function syncSubscriptionItem(Subscription $subscription, Plan $plan, BillingCycle $billingCycle): SubscriptionItem
    {
        $unitPrice = $billingCycle === BillingCycle::Yearly ? $plan->price_yearly : $plan->price_monthly;

        return SubscriptionItem::query()->updateOrCreate(
            [
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
            ],
            [
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice,
            ],
        );
    }

    /**
     * Generate an invoice for a subscription billing period.
     */
    public function issueBillingInvoice(
        Subscription $subscription,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): Invoice {
        $subscription->loadMissing('plan');
        $plan = $subscription->plan;

        if ($plan === null) {
            throw new InvalidArgumentException('Subscription has no plan assigned.');
        }

        $invoice = $this->generateInvoice(
            $subscription,
            $plan,
            $subscription->billing_cycle,
            $periodStart,
            $periodEnd,
        );

        $subscription->update(['latest_invoice_id' => $invoice->id]);

        return $invoice;
    }

    /**
     * Advance subscription billing period after successful payment.
     */
    public function advanceBillingPeriod(
        Subscription $subscription,
        Carbon $periodStart,
        Carbon $periodEnd,
        EventTriggeredBy $triggeredBy = EventTriggeredBy::System,
    ): Subscription {
        $subscription->loadMissing(['plan', 'tenant']);
        $plan = $subscription->plan;

        $subscription->update([
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'status' => SubscriptionStatus::Active,
            'trial_ends_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        $this->recordEvent($subscription, SubscriptionEventType::Renewed, $plan?->id, $plan?->id, $triggeredBy);

        $subscription->tenant?->update([
            'status' => TenantStatus::Active,
            'expires_at' => $periodEnd,
        ]);

        event(new SubscriptionRenewed($subscription->fresh(['tenant', 'plan', 'latestInvoice'])));

        return $subscription->fresh(['plan', 'latestInvoice', 'lifecycleEvents']);
    }

    /**
     * Calculate billing period end from cycle.
     */
    public function calculatePeriodEnd(Carbon $start, BillingCycle $billingCycle): Carbon
    {
        return $this->periodEnd($start, $billingCycle);
    }

    /**
     * Generate an invoice for a subscription billing period.
     */
    private function generateInvoice(
        Subscription $subscription,
        Plan $plan,
        BillingCycle $billingCycle,
        Carbon $periodStart,
        Carbon $periodEnd,
        ?string $notes = null,
    ): Invoice {
        $amount = $billingCycle === BillingCycle::Yearly ? $plan->price_yearly : $plan->price_monthly;

        $invoice = Invoice::query()->create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => $this->nextInvoiceNumber(),
            'status' => InvoiceStatus::Open,
            'amount_due' => $amount,
            'amount_paid' => 0,
            'amount_remaining' => $amount,
            'currency' => $plan->currency,
            'billing_period_start' => $periodStart,
            'billing_period_end' => $periodEnd,
            'due_date' => $periodStart->copy()->addDays(7),
            'notes' => $notes,
            'line_items' => [
                [
                    'description' => "{$plan->name} ({$billingCycle->value})",
                    'amount' => $amount,
                    'quantity' => 1,
                ],
            ],
        ]);

        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'plan_id' => $plan->id,
            'description' => "{$plan->name} subscription",
            'quantity' => 1,
            'unit_amount' => $amount,
            'amount' => $amount,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
        ]);

        return $invoice;
    }

    /**
     * Record a subscription lifecycle audit event.
     *
     * @param  array<string, mixed>|null  $metadata  Optional event metadata.
     */
    private function recordEvent(
        Subscription $subscription,
        SubscriptionEventType $eventType,
        ?string $fromPlanId,
        ?string $toPlanId,
        EventTriggeredBy $triggeredBy,
        ?array $metadata = null,
    ): SubscriptionEvent {
        return SubscriptionEvent::query()->create([
            'subscription_id' => $subscription->id,
            'event_type' => $eventType,
            'from_plan_id' => $fromPlanId,
            'to_plan_id' => $toPlanId,
            'triggered_by' => $triggeredBy,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Calculate billing period end from cycle.
     */
    private function periodEnd(Carbon $start, BillingCycle $billingCycle): Carbon
    {
        return $billingCycle === BillingCycle::Yearly
            ? $start->copy()->addYear()
            : $start->copy()->addMonth();
    }

    /**
     * Generate the next sequential invoice number.
     */
    private function nextInvoiceNumber(): string
    {
        $count = Invoice::query()->count() + 1;

        return sprintf('INV-%s-%06d', now()->format('Y'), $count);
    }
}
