<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant.
 */
class UpdateTenantRequest extends BaseRequest
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
             * Display name of the tenant organization; optional on update.
             *
             * @var string $name
             *
             * @example "Acme Corp"
             */
            'name' => 'sometimes|string|max:255',

            /**
             * URL-friendly unique identifier for the tenant; optional on update.
             *
             * @var string $slug
             *
             * @example "acme-corp"
             */
            'slug' => 'sometimes|string|unique:tenants,slug|max:255',

            /**
             * Name of the tenant's isolated database; optional on update.
             *
             * @var string $database
             *
             * @example "tenant_acme_corp"
             */
            'database' => 'sometimes|string|max:255',

            /**
             * Primary domain assigned to the tenant; optional on update.
             *
             * @var string $domain
             *
             * @example "acme.example.com"
             */
            'domain' => 'sometimes|string|max:255',

            /**
             * Current lifecycle status of the tenant; optional on update.
             *
             * @var string $status
             *
             * @example "suspended"
             */
            'status' => 'sometimes|string|in:pending,active,suspended,cancelled',

            /**
             * UUID of the tenant's current plan; nullable.
             *
             * @var string|null $plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'plan_id' => 'nullable|integer|exists:plans,id',

            /**
             * Recurring billing interval for the tenant's subscription; optional on update.
             *
             * @var string $billing_cycle
             *
             * @example "yearly"
             */
            'billing_cycle' => 'sometimes|string|in:monthly,yearly',

            /**
             * When the tenant's trial period ends; nullable.
             *
             * @var string|null $trial_ends_at
             *
             * @example "2026-02-15T00:00:00Z"
             */
            'trial_ends_at' => 'nullable|date',

            /**
             * When the tenant first subscribed to a paid plan; nullable.
             *
             * @var string|null $subscribed_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'subscribed_at' => 'nullable|date',

            /**
             * When the tenant's access expires; nullable.
             *
             * @var string|null $expires_at
             *
             * @example "2027-01-15T00:00:00Z"
             */
            'expires_at' => 'nullable|date',

            /**
             * Email address of the tenant account owner; optional on update.
             *
             * @var string $owner_email
             *
             * @example "user@example.com"
             */
            'owner_email' => 'sometimes|email',

            /**
             * Full name of the tenant account owner; optional on update.
             *
             * @var string $owner_name
             *
             * @example "John Smith"
             */
            'owner_name' => 'sometimes|string|max:255',

            /**
             * Tenant-specific configuration overrides; optional.
             *
             * @var array<string, mixed> $settings
             *
             * @example {"timezone": "America/New_York"}
             */
            'settings' => 'sometimes|array',

            /**
             * Additional metadata stored with the tenant; optional.
             *
             * @var array<string, mixed> $meta
             *
             * @example {"industry": "saas"}
             */
            'meta' => 'sometimes|array',

            /**
             * Arbitrary tenant payload data; optional.
             *
             * @var array<string, mixed> $data
             *
             * @example {"onboarding_step": 3}
             */
            'data' => 'sometimes|array',
        ];
    }
}
