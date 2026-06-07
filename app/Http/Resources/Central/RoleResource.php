<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Role
 */
class RoleResource extends JsonResource
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
             * Role name.
             *
             * @example "admin"
             */
            'name' => $this->name,

            /**
             * Authentication guard the role applies to.
             *
             * @example "web"
             */
            'guard_name' => $this->guard_name,

            /**
             * Timestamp when the role was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the role was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Permissions granted through this role when eager loaded.
             *
             * @default null
             */
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
        ];
    }
}
