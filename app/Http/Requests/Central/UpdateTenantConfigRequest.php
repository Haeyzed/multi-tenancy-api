<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant configuration entry.
 */
class UpdateTenantConfigRequest extends BaseRequest
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
             * UUID of the tenant this configuration belongs to; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * Configuration key identifier; optional on update.
             *
             * @var string $key
             *
             * @example "smtp.host"
             */
            'key' => 'sometimes|string|max:255',

            /**
             * Configuration value stored as a string; nullable.
             *
             * @var string|null $value
             *
             * @example "smtp.sendgrid.net"
             */
            'value' => 'nullable|string',

            /**
             * Whether the value should be stored encrypted; optional.
             *
             * @var bool $encrypted
             *
             * @example false
             */
            'encrypted' => 'sometimes|boolean',
        ];
    }
}
