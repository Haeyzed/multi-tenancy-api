<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new tenant API key.
 */
class StoreApiKeyRequest extends BaseRequest
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
             * UUID of the tenant that owns this API key.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * Human-readable label for the API key.
             *
             * @var string $name
             *
             * @example "Production Integration Key"
             */
            'name' => 'required|string|max:255',

            /**
             * Hashed value of the API key secret.
             *
             * @var string $key_hash
             *
             * @example "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"
             */
            'key_hash' => 'nullable|string',

            /**
             * Optional list of permission scopes granted to this key.
             *
             * @var array<int, string> $permissions
             *
             * @example ["orders.read", "products.write"]
             */
            'permissions' => 'sometimes|array',

            /**
             * Timestamp of the last time this key was used; nullable.
             *
             * @var string|null $last_used_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'last_used_at' => 'nullable|date',

            /**
             * Expiration timestamp after which the key is invalid; nullable.
             *
             * @var string|null $expires_at
             *
             * @example "2027-01-15T10:30:00Z"
             */
            'expires_at' => 'nullable|date',

            /**
             * Whether the API key is currently active; optional.
             *
             * @var bool $is_active
             *
             * @example true
             */
            'is_active' => 'sometimes|boolean',
        ];
    }
}
