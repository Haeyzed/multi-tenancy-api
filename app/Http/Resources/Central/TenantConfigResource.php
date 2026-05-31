<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\TenantConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantConfig
 */
class TenantConfigResource extends JsonResource
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
             * Identifier of the owning tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Configuration key.
             *
             * @example "mail.from_address"
             */
            'key' => $this->key,

            /**
             * Configuration value.
             *
             * @example "noreply@acme.com"
             */
            'value' => $this->value,

            /**
             * Whether the value is stored encrypted.
             *
             * @example false
             *
             * @default false
             */
            'encrypted' => (bool) $this->encrypted,

            /**
             * Timestamp when the configuration was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the configuration was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Owning tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
