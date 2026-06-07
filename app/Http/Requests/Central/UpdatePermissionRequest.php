<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Validation\Rule;

/**
 * Validates incoming data for updating an existing permission.
 */
class UpdatePermissionRequest extends BaseRequest
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
        /** @var \App\Models\Central\Permission|null $permission */
        $permission = $this->route('permission');

        return [
            /**
             * Unique permission identifier; must be unique across permissions; optional on update.
             *
             * @var string $name
             *
             * @example "tenants.manage"
             */
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permission?->id),
            ],

            /**
             * Authentication guard this permission applies to; optional on update.
             *
             * @var string $guard_name
             *
             * @example "web"
             */
            'guard_name' => 'sometimes|string|max:255',

            /**
             * Module or feature group this permission belongs to; nullable.
             *
             * @var string|null $module
             *
             * @example "billing"
             */
            'module' => 'nullable|string|max:255',
        ];
    }
}
