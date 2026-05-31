<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new central platform user.
 */
class StoreUserRequest extends FormRequest
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
             * Platform role assigned to the user.
             *
             * @var string $role
             *
             * @example "super_admin"
             */
            'role' => 'required|string|in:super_admin,support,billing,technical',

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
