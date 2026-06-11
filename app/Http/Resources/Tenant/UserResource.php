<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Enums\Tenant\TenantUserRole;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->name,
            'phone' => $this->phone,
            'avatar_media_id' => $this->avatar_media_id,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'is_active' => (bool)$this->is_active,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'role_names' => $this->when(
                $this->relationLoaded('roles'),
                fn(): array => $this->getRoleNames()->values()->all(),
            ),
            'permission_names' => $this->when(
                $this->relationLoaded('roles') || $this->relationLoaded('permissions'),
                fn(): array => $this->getAllPermissions()->pluck('name')->values()->all(),
            ),
            'is_store_owner' => $this->when(
                $this->relationLoaded('roles'),
                fn(): bool => $this->hasRole(TenantUserRole::StoreOwner->value),
            ),
        ];
    }
}
