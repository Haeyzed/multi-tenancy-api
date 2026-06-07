<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new permission.
 */
class StorePermissionRequest extends BaseRequest
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
             * Unique permission identifier; must be unique across permissions.
             *
             * @var string $name
             *
             * @example "tenants.manage"
             */
            'name' => 'required|string|max:255|unique:permissions,name',

            /**
             * Authentication guard this permission applies to.
             *
             * @var string $guard_name
             *
             * @example "web"
             */
            'guard_name' => 'required|string|max:255',

            /**
             * Module or feature group this permission belongs to; nullable.
             *
             * @var string|null $module
             *
             * @example "tenants"
             */
            'module' => 'nullable|string|max:255',
        ];
    }
}
