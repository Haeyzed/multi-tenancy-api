<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\PlanFeature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlanFeature
 */
class PlanFeatureResource extends JsonResource
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
             * Identifier of the parent plan.
             *
             * @example 2
             */
            'plan_id' => $this->plan_id,

            /**
             * Machine-readable feature key.
             *
             * @example "max_users"
             */
            'feature_key' => $this->feature_key,

            /**
             * Feature limit or value.
             *
             * @example "25"
             */
            'feature_value' => $this->feature_value,

            /**
             * Data type of the feature value.
             *
             * @example "integer"
             */
            'feature_type' => $this->feature_type,

            /**
             * Timestamp when the feature was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the feature was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Parent plan when eager loaded.
             *
             * @default null
             */
            'plan' => new PlanResource($this->whenLoaded('plan')),
        ];
    }
}
