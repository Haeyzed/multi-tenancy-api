<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant domain.
 */
class UpdateDomainRequest extends BaseRequest
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
             * UUID of the tenant that owns this domain; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * Fully qualified domain name; must be unique across all tenants; optional on update.
             *
             * @var string $domain
             *
             * @example "acme.example.com"
             */
            'domain' => 'sometimes|string|unique:domains,domain|max:255',

            /**
             * Whether this is the tenant's primary domain; optional.
             *
             * @var bool $is_primary
             *
             * @example true
             */
            'is_primary' => 'sometimes|boolean',

            /**
             * Whether this domain serves as a fallback; optional.
             *
             * @var bool $is_fallback
             *
             * @example false
             */
            'is_fallback' => 'sometimes|boolean',

            /**
             * Whether domain ownership has been verified; optional.
             *
             * @var bool $verified
             *
             * @example true
             */
            'verified' => 'sometimes|boolean',
        ];
    }
}
