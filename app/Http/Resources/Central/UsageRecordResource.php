<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\UsageRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UsageRecord
 */
class UsageRecordResource extends JsonResource
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
             * Identifier of the tenant being measured.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Identifier of the related subscription.
             *
             * @example 10
             *
             * @default null
             */
            'subscription_id' => $this->subscription_id,

            /**
             * Usage metric name.
             *
             * @example "api_calls"
             */
            'metric' => $this->metric,

            /**
             * Recorded quantity for the metric.
             *
             * @example 1500
             */
            'quantity' => $this->quantity,

            /**
             * Timestamp when usage was recorded.
             *
             * @example "2026-05-30T14:00:00+00:00"
             */
            'recorded_at' => $this->recorded_at?->toIso8601String(),

            /**
             * Timestamp when the record was created.
             *
             * @example "2026-05-30T14:00:01+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the record was last updated.
             *
             * @example "2026-05-30T14:00:01+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Measured tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),

            /**
             * Related subscription when eager loaded.
             *
             * @default null
             */
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),
        ];
    }
}
