<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plan
 */
class PlanResource extends JsonResource
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
             * Display name of the plan.
             *
             * @example "Professional"
             */
            'name' => $this->name,

            /**
             * URL-friendly unique slug.
             *
             * @example "professional"
             */
            'slug' => $this->slug,

            /**
             * Marketing description of the plan.
             *
             * @example "For growing businesses"
             *
             * @default null
             */
            'description' => $this->description,

            /**
             * Plan tier level.
             *
             * @example 2
             */
            'tier' => $this->tier,

            /**
             * Whether the plan is available for assignment.
             *
             * @example true
             *
             * @default true
             */
            'is_active' => (bool)$this->is_active,

            /**
             * Whether the plan is visible on public pricing pages.
             *
             * @example true
             *
             * @default true
             */
            'is_public' => (bool)$this->is_public,

            /**
             * Monthly price in the plan currency.
             *
             * @example "29.99"
             */
            'price_monthly' => $this->price_monthly,

            /**
             * Yearly price in the plan currency.
             *
             * @example "299.99"
             */
            'price_yearly' => $this->price_yearly,

            /**
             * ISO 4217 currency code.
             *
             * @example "USD"
             */
            'currency' => $this->currency,

            /**
             * Number of trial days offered.
             *
             * @example 14
             *
             * @default 0
             */
            'trial_days' => $this->trial_days,

            /**
             * Display order on pricing pages.
             *
             * @example 1
             *
             * @default 0
             */
            'sort_order' => $this->sort_order,

            /**
             * Marketing/display copy for pricing pages (not used for access control).
             *
             * @example {"highlights":["Up to 100 products","API access"]}
             *
             * @default null
             */
            'display_features' => $this->features,

            /**
             * Timestamp when the plan was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the plan was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Timestamp when the plan was soft deleted.
             *
             * @example "2026-02-01T12:00:00+00:00"
             *
             * @default null
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            /**
             * Tenants subscribed to this plan when eager loaded.
             *
             * @default null
             */
            'tenants' => TenantResource::collection($this->whenLoaded('tenants')),

            /**
             * Enforceable plan features (limits and flags) when eager loaded.
             *
             * @default null
             */
            'plan_features' => PlanFeatureResource::collection($this->whenLoaded('planFeatures')),

            /**
             * Subscriptions on this plan when eager loaded.
             *
             * @default null
             */
            'subscriptions' => SubscriptionResource::collection($this->whenLoaded('subscriptions')),

            /**
             * Subscription line items when eager loaded.
             *
             * @default null
             */
            'subscription_items' => SubscriptionItemResource::collection($this->whenLoaded('subscriptionItems')),

            /**
             * Invoice line items when eager loaded.
             *
             * @default null
             */
            'invoice_items' => InvoiceItemResource::collection($this->whenLoaded('invoiceItems')),

            /**
             * Subscription events where this plan was the source when eager loaded.
             *
             * @default null
             */
            'subscription_events_from_plan' => SubscriptionEventResource::collection($this->whenLoaded('subscriptionEventsFromPlan')),

            /**
             * Subscription events where this plan was the target when eager loaded.
             *
             * @default null
             */
            'subscription_events_to_plan' => SubscriptionEventResource::collection($this->whenLoaded('subscriptionEventsToPlan')),
        ];
    }
}
