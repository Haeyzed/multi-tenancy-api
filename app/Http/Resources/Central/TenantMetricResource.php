<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\TenantMetric;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantMetric
 */
class TenantMetricResource extends JsonResource
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
             * Identifier of the measured tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Calendar date the metrics represent.
             *
             * @example "2026-05-30T00:00:00+00:00"
             */
            'metric_date' => $this->metric_date?->toIso8601String(),

            /**
             * Total orders recorded for the day.
             *
             * @example 150
             *
             * @default 0
             */
            'total_orders' => $this->total_orders,

            /**
             * Total revenue recorded for the day.
             *
             * @example "12500.00"
             *
             * @default "0.00"
             */
            'total_revenue' => $this->total_revenue,

            /**
             * Total products in the tenant catalog.
             *
             * @example 320
             *
             * @default 0
             */
            'total_products' => $this->total_products,

            /**
             * Total customers in the tenant database.
             *
             * @example 890
             *
             * @default 0
             */
            'total_customers' => $this->total_customers,

            /**
             * Storage consumed in megabytes.
             *
             * @example 512
             *
             * @default 0
             */
            'storage_used_mb' => $this->storage_used_mb,

            /**
             * Bandwidth consumed in megabytes.
             *
             * @example 1024
             *
             * @default 0
             */
            'bandwidth_used_mb' => $this->bandwidth_used_mb,

            /**
             * Number of API calls made during the day.
             *
             * @example 4500
             *
             * @default 0
             */
            'api_calls' => $this->api_calls,

            /**
             * Timestamp when the metric row was created.
             *
             * @example "2026-05-31T01:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the metric row was last updated.
             *
             * @example "2026-05-31T01:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Measured tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
