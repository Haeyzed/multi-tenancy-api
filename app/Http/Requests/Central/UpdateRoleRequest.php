<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Validation\Rule;

/**
 * Validates incoming data for updating an existing role.
 */
class UpdateRoleRequest extends BaseRequest
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
        /** @var \App\Models\Central\Role|null $role */
        $role = $this->route('role');

        return [
            /**
             * Unique role name; must be unique across roles; optional on update.
             *
             * @var string $name
             *
             * @example "platform-admin"
             */
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],

            /**
             * Authentication guard this role applies to; optional on update.
             *
             * @var string $guard_name
             *
             * @example "web"
             */
            'guard_name' => 'sometimes|string|max:255',
        ];
    }
}
