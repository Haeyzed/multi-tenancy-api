<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates syncing Spatie roles onto a user.
 */
class SyncUserRolesRequest extends BaseRequest
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
            'role_ids' => 'present|array',
            'role_ids.*' => 'integer|exists:roles,id',
        ];
    }
}
