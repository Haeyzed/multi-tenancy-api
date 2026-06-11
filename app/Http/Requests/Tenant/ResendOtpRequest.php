<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Enums\Tenant\OtpPurpose;
use Illuminate\Validation\Rule;

/**
 * Validates an OTP resend request.
 */
class ResendOtpRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'purpose' => ['required', Rule::enum(OtpPurpose::class)],
        ];
    }
}
