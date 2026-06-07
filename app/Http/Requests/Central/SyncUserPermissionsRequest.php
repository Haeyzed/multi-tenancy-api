<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates syncing direct Spatie permissions onto a user.
 */
class SyncUserPermissionsRequest extends BaseRequest
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
            'permission_ids' => 'present|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
