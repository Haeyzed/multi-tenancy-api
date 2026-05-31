<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new tenant configuration entry.
 */
class StoreTenantConfigRequest extends FormRequest
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
             * UUID of the tenant this configuration belongs to.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * Configuration key identifier.
             *
             * @var string $key
             *
             * @example "smtp.host"
             */
            'key' => 'required|string|max:255',

            /**
             * Configuration value stored as a string; nullable.
             *
             * @var string|null $value
             *
             * @example "smtp.mailgun.org"
             */
            'value' => 'nullable|string',

            /**
             * Whether the value should be stored encrypted; optional.
             *
             * @var bool $encrypted
             *
             * @example true
             */
            'encrypted' => 'sometimes|boolean',
        ];
    }
}
