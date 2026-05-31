<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\SubscriptionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SubscriptionItem
 */
class SubscriptionItemResource extends JsonResource
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
             * Identifier of the parent subscription.
             *
             * @example 10
             */
            'subscription_id' => $this->subscription_id,

            /**
             * Identifier of the billed plan.
             *
             * @example 2
             */
            'plan_id' => $this->plan_id,

            /**
             * Quantity of plan units billed.
             *
             * @example 1
             *
             * @default 1
             */
            'quantity' => $this->quantity,

            /**
             * Price per unit in the subscription currency.
             *
             * @example "29.99"
             */
            'unit_price' => $this->unit_price,

            /**
             * Total line price before tax.
             *
             * @example "29.99"
             */
            'total_price' => $this->total_price,

            /**
             * Timestamp when the line item was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the line item was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Parent subscription when eager loaded.
             *
             * @default null
             */
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),

            /**
             * Billed plan when eager loaded.
             *
             * @default null
             */
            'plan' => new PlanResource($this->whenLoaded('plan')),
        ];
    }
}
