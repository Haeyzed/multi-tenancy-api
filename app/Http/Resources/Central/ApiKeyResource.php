<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ApiKey
 */
class ApiKeyResource extends JsonResource
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
             * Human-readable label for the API key.
             *
             * @example "Production integration"
             */
            'name' => $this->name,

            /**
             * Scoped permissions granted to the key.
             *
             * @example ["read:orders","write:products"]
             *
             * @default null
             */
            'permissions' => $this->permissions,

            /**
             * Timestamp when the key was last used.
             *
             * @example "2026-05-30T14:00:00+00:00"
             *
             * @default null
             */
            'last_used_at' => $this->last_used_at?->toIso8601String(),

            /**
             * Timestamp when the key expires.
             *
             * @example "2027-01-01T00:00:00+00:00"
             *
             * @default null
             */
            'expires_at' => $this->expires_at?->toIso8601String(),

            /**
             * Whether the key is currently active.
             *
             * @example true
             *
             * @default true
             */
            'is_active' => (bool)$this->is_active,

            /**
             * Plain-text key returned only once after creation.
             *
             * @example "ak_live_abc123..."
             *
             * @default null
             */
            'plain_key' => $this->when(
                isset($this->plain_key),
                fn() => $this->plain_key,
            ),

            /**
             * Timestamp when the key was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the key was last updated.
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
