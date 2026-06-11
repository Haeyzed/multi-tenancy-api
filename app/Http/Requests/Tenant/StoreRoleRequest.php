<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for creating a new store role.
 */
class StoreRoleRequest extends BaseRequest
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
             * Unique role name; must be unique across roles.
             *
             * @var string $name
             *
             * @example "manager"
             */
            'name' => 'required|string|max:255|unique:roles,name',

            /**
             * Authentication guard this role applies to.
             *
             * @var string $guard_name
             *
             * @example "tenant"
             */
            'guard_name' => 'required|string|max:255',
        ];
    }
}
