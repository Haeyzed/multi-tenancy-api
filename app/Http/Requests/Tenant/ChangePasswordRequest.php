<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates an authenticated password change request.
 */
class ChangePasswordRequest extends BaseRequest
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
            'current_password' => 'required_without:verification_token|nullable|string',
            'verification_token' => 'required_without:current_password|nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
