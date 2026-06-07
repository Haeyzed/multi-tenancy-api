<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Enums\Central\UserRole;
use App\Models\Central\User;
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
             * @example 1
             */
            'id' => $this->id,

            /**
             * Display name of the user.
             *
             * @example "Jane Admin"
             */
            'name' => $this->name,

            /**
             * Email address used for login and notifications.
             *
             * @example "admin@example.com"
             */
            'email' => $this->email,

            /**
             * Timestamp when the email address was verified.
             *
             * @example "2026-01-15T10:30:00+00:00"
             *
             * @default null
             */
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),

            /**
             * Timestamp of the user's most recent login.
             *
             * @example "2026-05-30T14:00:00+00:00"
             *
             * @default null
             */
            'last_login_at' => $this->last_login_at?->toIso8601String(),

            /**
             * Whether the user account is active.
             *
             * @example true
             *
             * @default true
             */
            'is_active' => (bool) $this->is_active,

            /**
             * Timestamp when the user was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the user was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Timestamp when the user was soft deleted.
             *
             * @example "2026-02-01T12:00:00+00:00"
             *
             * @default null
             */
            'deleted_at' => $this->deleted_at?->toIso8601String(),

            /**
             * Support tickets assigned to this user when eager loaded.
             *
             * @default null
             */
            'assigned_support_tickets' => TenantSupportTicketResource::collection($this->whenLoaded('assignedSupportTickets')),

            /**
             * Impersonation tokens issued by this user when eager loaded.
             *
             * @default null
             */
            'issued_impersonation_tokens' => TenantImpersonationTokenResource::collection($this->whenLoaded('issuedImpersonationTokens')),

            /**
             * Support messages sent by this user when eager loaded.
             *
             * @default null
             */
            'sent_support_messages' => TenantSupportMessageResource::collection($this->whenLoaded('sentSupportMessages')),

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
             * Effective permission names (direct + via roles) when roles or permissions are loaded.
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
             * Whether the user has the super admin role.
             *
             * @default null
             */
            'is_super_admin' => $this->when(
                $this->relationLoaded('roles'),
                fn (): bool => $this->hasRole(UserRole::SuperAdmin->value),
            ),
        ];
    }
}
