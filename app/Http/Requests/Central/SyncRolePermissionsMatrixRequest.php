<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates bulk syncing of role permissions from the matrix UI.
 */
class SyncRolePermissionsMatrixRequest extends BaseRequest
{
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
             * Role-permission assignments to sync in a single request.
             *
             * @var list<array{role_id: int, permission_ids: list<int>}> $roles
             *
             * @example [{"role_id": 2, "permission_ids": [1, 3, 5]}]
             */
            'roles' => 'required|array|min:1',

            /**
             * Spatie role ID for one matrix row.
             *
             * @var int $role_id
             *
             * @example 2
             */
            'roles.*.role_id' => 'required|integer|exists:roles,id',

            /**
             * Permission IDs assigned to the role; system roles may send an empty array.
             *
             * @var list<int> $permission_ids
             *
             * @example [1, 3, 5, 8]
             */
            'roles.*.permission_ids' => 'present|array',

            /**
             * Individual permission ID within a role assignment.
             *
             * @var int $permission_id
             *
             * @example 3
             */
            'roles.*.permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
