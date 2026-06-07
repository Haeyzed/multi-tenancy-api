<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates an authenticated password change request.
 */
class ChangePasswordRequest extends BaseRequest
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
             * Current account password; required when verification_token is absent.
             *
             * @var string|null $current_password
             */
            'current_password' => 'required_without:verification_token|nullable|string',

            /**
             * Token from OTP verification for password_change; required when current_password is absent.
             *
             * @var string|null $verification_token
             */
            'verification_token' => 'required_without:current_password|nullable|string',

            /**
             * New account password.
             *
             * @var string $password
             */
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
