<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates credentials for central platform login.
 */
class LoginRequest extends FormRequest
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
             * Email address used for authentication.
             *
             * @var string $email
             *
             * @example "admin@example.com"
             */
            'email' => 'required|email',

            /**
             * Account password.
             *
             * @var string $password
             *
             * @example "SecurePass123"
             */
            'password' => 'required|string',
        ];
    }
}
