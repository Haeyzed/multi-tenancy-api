<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant impersonation token.
 */
class UpdateTenantImpersonationTokenRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * UUID of the tenant to impersonate; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * ID of the platform admin issuing the impersonation token; optional on update.
             *
             * @var int $admin_id
             *
             * @example 1
             */
            'admin_id' => 'sometimes|exists:users,id',

            /**
             * One-time impersonation token; must be exactly 64 characters and unique; optional on update.
             *
             * @var string $token
             *
             * @example "a1b2c3d4e5f6789012345678901234567890123456789012345678901234abcd"
             */
            'token' => 'sometimes|string|size:64|unique:tenant_impersonation_tokens,token',

            /**
             * Timestamp when the token expires and can no longer be used; optional on update.
             *
             * @var string $expires_at
             *
             * @example "2026-01-15T11:30:00Z"
             */
            'expires_at' => 'sometimes|date',

            /**
             * Timestamp when the token was consumed; nullable until used.
             *
             * @var string|null $used_at
             *
             * @example "2026-01-15T10:45:00Z"
             */
            'used_at' => 'nullable|date',
        ];
    }
}
