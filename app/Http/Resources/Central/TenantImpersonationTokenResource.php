<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\TenantImpersonationToken;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantImpersonationToken
 */
class TenantImpersonationTokenResource extends JsonResource
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
             * Identifier of the target tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Identifier of the admin user who issued the token.
             *
             * @example 7
             */
            'admin_id' => $this->admin_id,

            /**
             * Timestamp when the token expires.
             *
             * @example "2026-05-31T12:00:00+00:00"
             */
            'expires_at' => $this->expires_at?->toIso8601String(),

            /**
             * Timestamp when the token was consumed.
             *
             * @example "2026-05-30T14:00:00+00:00"
             *
             * @default null
             */
            'used_at' => $this->used_at?->toIso8601String(),

            /**
             * Timestamp when the token was created.
             *
             * @example "2026-05-30T13:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the token was last updated.
             *
             * @example "2026-05-30T14:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Target tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),

            /**
             * Admin user who issued the token when eager loaded.
             *
             * @default null
             */
            'administrator' => new UserResource($this->whenLoaded('administrator')),
        ];
    }
}
