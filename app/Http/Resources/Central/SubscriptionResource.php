<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * Identifier of the subscribed tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Identifier of the subscribed plan.
             *
             * @example 2
             */
            'plan_id' => $this->plan_id,

            /**
             * Current subscription status.
             *
             * @example "active"
             */
            'status' => $this->status,

            /**
             * Billing cycle for the subscription.
             *
             * @example "monthly"
             */
            'billing_cycle' => $this->billing_cycle,

            /**
             * Start of the current billing period.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'current_period_start' => $this->current_period_start?->toIso8601String(),

            /**
             * End of the current billing period.
             *
             * @example "2026-06-01T00:00:00+00:00"
             */
            'current_period_end' => $this->current_period_end?->toIso8601String(),

            /**
             * Timestamp when the trial period ends.
             *
             * @example "2026-02-01T00:00:00+00:00"
             *
             * @default null
             */
            'trial_ends_at' => $this->trial_ends_at?->toIso8601String(),

            /**
             * Timestamp when the subscription was cancelled.
             *
             * @example "2026-04-01T12:00:00+00:00"
             *
             * @default null
             */
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),

            /**
             * Reason provided for cancellation.
             *
             * @example "Switching providers"
             *
             * @default null
             */
            'cancellation_reason' => $this->cancellation_reason,

            /**
             * External payment provider name.
             *
             * @example "stripe"
             *
             * @default null
             */
            'payment_provider' => $this->payment_provider,

            /**
             * External subscription identifier at the payment provider.
             *
             * @example "sub_1234567890"
             *
             * @default null
             */
            'payment_provider_id' => $this->payment_provider_id,

            /**
             * Identifier of the default payment method.
             *
             * @example 5
             *
             * @default null
             */
            'payment_method_id' => $this->payment_method_id,

            /**
             * Identifier of the most recent invoice.
             *
             * @example 100
             *
             * @default null
             */
            'latest_invoice_id' => $this->latest_invoice_id,

            /**
             * Timestamp when the subscription was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the subscription was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Subscribed tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),

            /**
             * Subscribed plan when eager loaded.
             *
             * @default null
             */
            'plan' => new PlanResource($this->whenLoaded('plan')),

            /**
             * Most recent invoice when eager loaded.
             *
             * @default null
             */
            'latest_invoice' => new InvoiceResource($this->whenLoaded('latestInvoice')),

            /**
             * All invoices for the subscription when eager loaded.
             *
             * @default null
             */
            'invoices' => InvoiceResource::collection($this->whenLoaded('invoices')),

            /**
             * Line items on the subscription when eager loaded.
             *
             * @default null
             */
            'subscription_items' => SubscriptionItemResource::collection($this->whenLoaded('subscriptionItems')),

            /**
             * Usage records for the subscription when eager loaded.
             *
             * @default null
             */
            'usage_records' => UsageRecordResource::collection($this->whenLoaded('usageRecords')),

            /**
             * Lifecycle events for the subscription when eager loaded.
             *
             * @default null
             */
            'lifecycle_events' => SubscriptionEventResource::collection($this->whenLoaded('lifecycleEvents')),
        ];
    }
}
