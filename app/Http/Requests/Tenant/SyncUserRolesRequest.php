<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates syncing Spatie roles onto a store user.
 */
class SyncUserRolesRequest extends BaseRequest
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
             * Role IDs to assign (replaces existing roles).
             *
             * @var list<int> $role_ids
             */
            'role_ids' => 'present|array',
            'role_ids.*' => 'integer|exists:roles,id',
        ];
    }
}
