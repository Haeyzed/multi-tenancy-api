<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates a password reset after OTP verification.
 */
class ResetPasswordRequest extends BaseRequest
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
            'email' => 'required|email',
            'verification_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
