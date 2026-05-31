<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\SubscriptionEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SubscriptionEvent
 */
class SubscriptionEventResource extends JsonResource
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
             * Identifier of the related subscription.
             *
             * @example 10
             */
            'subscription_id' => $this->subscription_id,

            /**
             * Type of lifecycle event.
             *
             * @example "plan_changed"
             */
            'event_type' => $this->event_type,

            /**
             * Plan identifier before the change.
             *
             * @example 1
             *
             * @default null
             */
            'from_plan_id' => $this->from_plan_id,

            /**
             * Plan identifier after the change.
             *
             * @example 2
             *
             * @default null
             */
            'to_plan_id' => $this->to_plan_id,

            /**
             * Actor or system that triggered the event.
             *
             * @example "admin"
             */
            'triggered_by' => $this->triggered_by,

            /**
             * Additional event metadata.
             *
             * @example {"reason":"upgrade"}
             *
             * @default null
             */
            'metadata' => $this->metadata,

            /**
             * Timestamp when the event was recorded.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the event was last updated.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Related subscription when eager loaded.
             *
             * @default null
             */
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),

            /**
             * Source plan when eager loaded.
             *
             * @default null
             */
            'from_plan' => new PlanResource($this->whenLoaded('fromPlan')),

            /**
             * Target plan when eager loaded.
             *
             * @default null
             */
            'to_plan' => new PlanResource($this->whenLoaded('toPlan')),
        ];
    }
}
