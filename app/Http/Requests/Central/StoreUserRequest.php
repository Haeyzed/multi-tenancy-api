<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new central platform user.
 */
class StoreUserRequest extends BaseRequest
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
             * Full display name of the platform user.
             *
             * @var string $name
             *
             * @example "Jane Admin"
             */
            'name' => 'required|string|max:255',

            /**
             * Unique email address used for login and notifications.
             *
             * @var string $email
             *
             * @example "user@example.com"
             */
            'email' => 'required|email|unique:users,email',

            /**
             * Plain-text password; must be at least 8 characters.
             *
             * @var string $password
             *
             * @example "SecurePass123"
             */
            'password' => 'required|string|min:8',

            /**
             * Whether the user account is active; optional.
             *
             * @var bool $is_active
             *
             * @example true
             */
            'is_active' => 'sometimes|boolean',

            /**
             * Spatie role IDs to assign to the user.
             *
             * @var list<int> $role_ids
             */
            'role_ids' => 'sometimes|array',
            'role_ids.*' => 'integer|exists:roles,id',

            /**
             * Direct Spatie permission IDs to assign to the user.
             *
             * @var list<int> $permission_ids
             */
            'permission_ids' => 'sometimes|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
