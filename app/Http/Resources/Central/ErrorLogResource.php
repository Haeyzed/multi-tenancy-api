<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ErrorLog
 */
class ErrorLogResource extends JsonResource
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
             * Identifier of the affected tenant, if applicable.
             *
             * @example 42
             *
             * @default null
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Error severity level.
             *
             * @example "error"
             */
            'severity' => $this->severity,

            /**
             * Logging channel or source.
             *
             * @example "api"
             */
            'channel' => $this->channel,

            /**
             * Error message text.
             *
             * @example "Database connection timeout"
             */
            'message' => $this->message,

            /**
             * Structured context captured with the error.
             *
             * @example {"url":"/api/orders","method":"GET"}
             *
             * @default null
             */
            'context' => $this->context,

            /**
             * Timestamp when the error occurred.
             *
             * @example "2026-05-30T14:00:00+00:00"
             */
            'occurred_at' => $this->occurred_at?->toIso8601String(),

            /**
             * Timestamp when the error was marked resolved.
             *
             * @example "2026-05-30T15:00:00+00:00"
             *
             * @default null
             */
            'resolved_at' => $this->resolved_at?->toIso8601String(),

            /**
             * Timestamp when the log entry was created.
             *
             * @example "2026-05-30T14:00:01+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the log entry was last updated.
             *
             * @example "2026-05-30T15:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Affected tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
