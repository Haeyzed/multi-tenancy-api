<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates attaching permissions to a store role without removing existing ones.
 */
class AttachRolePermissionsRequest extends BaseRequest
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
             * Permission IDs to attach to the role.
             *
             * @var list<int> $permission_ids
             *
             * @example [4, 5]
             */
            'permission_ids' => 'required|array|min:1',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
