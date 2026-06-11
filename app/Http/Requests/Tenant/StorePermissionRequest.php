<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for creating a new store permission.
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
             * @example "catalog.manage"
             */
            'name' => 'required|string|max:255|unique:permissions,name',

            /**
             * Authentication guard this permission applies to.
             *
             * @var string $guard_name
             *
             * @example "tenant"
             */
            'guard_name' => 'required|string|max:255',

            /**
             * Module or feature group this permission belongs to; nullable.
             *
             * @var string|null $module
             *
             * @example "catalog"
             */
            'module' => 'nullable|string|max:255',
        ];
    }
}
