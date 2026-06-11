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
             * @example "9f3c2b1a-4d5e-6f7a-8b9c-0d1e2f3a4b5c"
             */
            'id' => $this->id,

            /**
             * Email address used for login and notifications.
             *
             * @example "staff@store.example.com"
             */
            'email' => $this->email,

            /**
             * User's first name.
             *
             * @example "Jane"
             */
            'first_name' => $this->first_name,

            /**
             * User's last name.
             *
             * @example "Doe"
             */
            'last_name' => $this->last_name,

            /**
             * Full display name (first + last).
             *
             * @example "Jane Doe"
             */
            'name' => $this->name,

            /**
             * Contact phone number.
             *
             * @default null
             */
            'phone' => $this->phone,

            /**
             * Media library ID for the avatar.
             *
             * @default null
             */
            'avatar_media_id' => $this->avatar_media_id,

            /**
             * Timestamp when the email address was verified.
             *
             * @default null
             */
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),

            /**
             * Timestamp of the user's most recent login.
             *
             * @default null
             */
            'last_login_at' => $this->last_login_at?->toIso8601String(),

            /**
             * Whether the user account is active.
             *
             * @example true
             */
            'is_active' => (bool) $this->is_active,

            /**
             * Preferred locale.
             *
             * @example "en"
             */
            'locale' => $this->locale,

            /**
             * Preferred timezone.
             *
             * @example "UTC"
             */
            'timezone' => $this->timezone,

            /**
             * Timestamp when the user was created.
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the user was last updated.
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Spatie roles assigned to this user when eager loaded.
             *
             * @default null
             */
            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            /**
             * Direct Spatie permissions assigned to this user when eager loaded.
             *
             * @default null
             */
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),

            /**
             * Role names when roles are eager loaded.
             *
             * @default null
             *
             * @var list<string>|null
             */
            'role_names' => $this->when(
                $this->relationLoaded('roles'),
                fn (): array => $this->getRoleNames()->values()->all(),
            ),

            /**
             * Effective permission names when roles or permissions are loaded.
             *
             * @default null
             *
             * @var list<string>|null
             */
            'permission_names' => $this->when(
                $this->relationLoaded('roles') || $this->relationLoaded('permissions'),
                fn (): array => $this->getAllPermissions()->pluck('name')->values()->all(),
            ),

            /**
             * Whether the user has the store owner role.
             *
             * @default null
             */
            'is_store_owner' => $this->when(
                $this->relationLoaded('roles'),
                fn (): bool => $this->hasRole(TenantUserRole::StoreOwner->value),
            ),
        ];
    }
}
