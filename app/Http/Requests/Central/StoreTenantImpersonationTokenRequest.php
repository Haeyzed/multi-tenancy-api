<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new tenant impersonation token.
 */
class StoreTenantImpersonationTokenRequest extends BaseRequest
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
             * UUID of the tenant to impersonate.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * ID of the platform admin issuing the impersonation token.
             *
             * @var int $admin_id
             *
             * @example 1
             */
            'admin_id' => 'required|exists:users,id',

            /**
             * One-time impersonation token; must be exactly 64 characters and unique.
             *
             * @var string $token
             *
             * @example "a1b2c3d4e5f6789012345678901234567890123456789012345678901234abcd"
             */
            'token' => 'nullable|string|size:64|unique:tenant_impersonation_tokens,token',

            /**
             * Timestamp when the token expires and can no longer be used.
             *
             * @var string $expires_at
             *
             * @example "2026-01-15T11:30:00Z"
             */
            'expires_at' => 'required|date',

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
