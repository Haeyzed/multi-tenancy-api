<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates a password reset after OTP verification.
 */
class ResetPasswordRequest extends BaseRequest
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
             * Email address associated with the reset flow.
             *
             * @var string $email
             *
             * @example "admin@platform.com"
             */
            'email' => 'required|email',

            /**
             * Token returned from OTP verification.
             *
             * @var string $verification_token
             */
            'verification_token' => 'required|string',

            /**
             * New account password.
             *
             * @var string $password
             */
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
