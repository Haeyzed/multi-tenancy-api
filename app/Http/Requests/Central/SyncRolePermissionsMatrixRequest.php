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
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'roles' => 'required|array|min:1',
            'roles.*.role_id' => 'required|integer|exists:roles,id',
            'roles.*.permission_ids' => 'present|array',
            'roles.*.permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
