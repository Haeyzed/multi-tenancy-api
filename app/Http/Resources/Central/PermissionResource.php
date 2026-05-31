<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Permission
 */
class PermissionResource extends JsonResource
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
             * Permission name.
             *
             * @example "tenants.view"
             */
            'name' => $this->name,

            /**
             * Authentication guard the permission applies to.
             *
             * @example "web"
             */
            'guard_name' => $this->guard_name,

            /**
             * Functional module grouping for the permission.
             *
             * @example "tenants"
             *
             * @default null
             */
            'module' => $this->module,

            /**
             * Timestamp when the permission was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the permission was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
