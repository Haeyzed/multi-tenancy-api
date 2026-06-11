<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;

/**
 * Validates incoming data for creating a store staff user.
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
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            /**
             * Unique email address used for login and notifications.
             *
             * @var string $email
             *
             * @example "staff@store.example.com"
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
             * User's first name.
             *
             * @var string $first_name
             *
             * @example "Jane"
             */
            'first_name' => 'required|string|max:255',

            /**
             * User's last name.
             *
             * @var string $last_name
             *
             * @example "Doe"
             */
            'last_name' => 'required|string|max:255',

            /**
             * Contact phone number; nullable.
             *
             * @var string|null $phone
             */
            'phone' => 'nullable|string|max:50',

            /**
             * Media library ID for the avatar; nullable.
             *
             * @var int|null $avatar_media_id
             */
            'avatar_media_id' => 'nullable|integer|exists:media,id',

            /**
             * Date of birth; nullable.
             *
             * @var string|null $birth_date
             */
            'birth_date' => 'nullable|date',

            /**
             * Gender; nullable.
             *
             * @var string|null $gender
             */
            'gender' => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not'])],

            /**
             * Preferred locale; optional.
             *
             * @var string $locale
             *
             * @example "en"
             */
            'locale' => 'sometimes|string|max:10',

            /**
             * Preferred timezone; optional.
             *
             * @var string $timezone
             *
             * @example "UTC"
             */
            'timezone' => 'sometimes|string|max:64',

            /**
             * Whether the user account is active; optional.
             *
             * @var bool $is_active
             *
             * @example true
             */
            'is_active' => 'sometimes|boolean',

            /**
             * Whether the user opted in to marketing; optional.
             *
             * @var bool $is_marketing_opt_in
             */
            'is_marketing_opt_in' => 'sometimes|boolean',

            /**
             * Internal notes about the user; nullable.
             *
             * @var string|null $notes
             */
            'notes' => 'nullable|string',

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
