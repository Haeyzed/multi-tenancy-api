<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use App\Enums\Central\OtpPurpose;
use Illuminate\Validation\Rule;

/**
 * Validates an OTP resend request.
 */
class ResendOtpRequest extends BaseRequest
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
             * OTP flow this resend belongs to.
             *
             * @var string $purpose
             *
             * @example "password_reset"
             */
            'purpose' => ['required', Rule::enum(OtpPurpose::class)],
        ];
    }
}
