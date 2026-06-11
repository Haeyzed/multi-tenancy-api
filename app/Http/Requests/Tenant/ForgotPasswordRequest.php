<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates a forgot-password request.
 */
class ForgotPasswordRequest extends BaseRequest
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
             * Email address for the account to recover.
             *
             * @var string $email
             *
             * @example "owner@store.com"
             */
            'email' => 'required|email',
        ];
    }
}
