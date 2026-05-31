<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for updating an existing central platform user.
 */
class UpdateUserRequest extends FormRequest
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
             * Full display name of the platform user; optional on update.
             *
             * @var string $name
             *
             * @example "Jane Admin"
             */
            'name' => 'sometimes|string|max:255',

            /**
             * Unique email address used for login and notifications; optional on update.
             *
             * @var string $email
             *
             * @example "user@example.com"
             */
            'email' => 'sometimes|email|unique:users,email',

            /**
             * New plain-text password; must be at least 8 characters when provided.
             *
             * @var string $password
             *
             * @example "SecurePass123"
             */
            'password' => 'sometimes|string|min:8',

            /**
             * Platform role assigned to the user; optional on update.
             *
             * @var string $role
             *
             * @example "support"
             */
            'role' => 'sometimes|string|in:super_admin,support,billing,technical',

            /**
             * Whether the user account is active; optional.
             *
             * @var bool $is_active
             *
             * @example true
             */
            'is_active' => 'sometimes|boolean',
        ];
    }
}
