<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates syncing permissions onto a role.
 */
class SyncRolePermissionsRequest extends BaseRequest
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
             * Permission IDs to assign to the role (replaces existing permissions).
             *
             * @var list<int> $permission_ids
             *
             * @example [1, 2, 3]
             */
            'permission_ids' => 'present|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
