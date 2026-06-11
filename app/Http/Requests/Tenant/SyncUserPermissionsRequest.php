<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates syncing direct Spatie permissions onto a store user.
 */
class SyncUserPermissionsRequest extends BaseRequest
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
             * Permission IDs to assign directly (replaces existing direct permissions).
             *
             * @var list<int> $permission_ids
             */
            'permission_ids' => 'present|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
