<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\User;
use Illuminate\Validation\Rule;

/**
 * Validates incoming data for updating a store staff user.
 */
class UpdateUserRequest extends BaseRequest
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
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            /**
             * Unique email address; optional on update.
             *
             * @var string $email
             *
             * @example "staff@store.example.com"
             */
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],

            /**
             * New plain-text password; optional on update.
             *
             * @var string $password
             */
            'password' => 'sometimes|string|min:8',

            /**
             * User's first name; optional on update.
             *
             * @var string $first_name
             */
            'first_name' => 'sometimes|string|max:255',

            /**
             * User's last name; optional on update.
             *
             * @var string $last_name
             */
            'last_name' => 'sometimes|string|max:255',

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
             */
            'locale' => 'sometimes|string|max:10',

            /**
             * Preferred timezone; optional.
             *
             * @var string $timezone
             */
            'timezone' => 'sometimes|string|max:64',

            /**
             * Whether the user account is active; optional.
             *
             * @var bool $is_active
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
