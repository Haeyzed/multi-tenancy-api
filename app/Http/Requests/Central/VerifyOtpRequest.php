<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use App\Enums\Central\OtpPurpose;
use Illuminate\Validation\Rule;

/**
 * Validates an OTP verification request.
 */
class VerifyOtpRequest extends BaseRequest
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
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            /**
             * Email address the OTP was sent to.
             *
             * @var string $email
             *
             * @example "admin@platform.com"
             */
            'email' => 'required|email',

            /**
             * One-time verification code from email.
             *
             * @var string $otp
             *
             * @example "123456"
             */
            'otp' => 'required|string|digits:' . config('otp.length'),

            /**
             * OTP flow being verified.
             *
             * @var string $purpose
             *
             * @example "password_reset"
             */
            'purpose' => ['required', Rule::enum(OtpPurpose::class)],
        ];
    }
}
