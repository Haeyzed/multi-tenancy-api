<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Domain
 */
class DomainResource extends JsonResource
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
             * Domain hostname.
             *
             * @example "acme.example.com"
             */
            'domain' => $this->domain,

            /**
             * Whether this is the tenant's primary domain.
             *
             * @example true
             *
             * @default false
             */
            'is_primary' => (bool) $this->is_primary,

            /**
             * Whether this domain is used as a fallback.
             *
             * @example false
             *
             * @default false
             */
            'is_fallback' => (bool) $this->is_fallback,

            /**
             * Whether domain ownership has been verified.
             *
             * @example true
             *
             * @default false
             */
            'verified' => (bool) $this->verified,

            /**
             * Timestamp when the domain was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the domain was last updated.
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
