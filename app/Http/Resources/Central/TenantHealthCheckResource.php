<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\TenantHealthCheck;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantHealthCheck
 */
class TenantHealthCheckResource extends JsonResource
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
             * Identifier of the checked tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Type of health check performed.
             *
             * @example "database"
             */
            'check_type' => $this->check_type,

            /**
             * Result status of the health check.
             *
             * @example "healthy"
             */
            'status' => $this->status,

            /**
             * Response time in milliseconds.
             *
             * @example 45
             *
             * @default null
             */
            'response_time_ms' => $this->response_time_ms,

            /**
             * Human-readable result message.
             *
             * @example "Connection successful"
             *
             * @default null
             */
            'message' => $this->message,

            /**
             * Timestamp when the check was performed.
             *
             * @example "2026-05-30T14:00:00+00:00"
             */
            'checked_at' => $this->checked_at?->toIso8601String(),

            /**
             * Timestamp when the check record was created.
             *
             * @example "2026-05-30T14:00:01+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the check record was last updated.
             *
             * @example "2026-05-30T14:00:01+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Checked tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
