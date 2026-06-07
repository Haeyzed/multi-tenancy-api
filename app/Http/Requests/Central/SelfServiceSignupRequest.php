<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates self-service tenant signup payload.
 */
class SelfServiceSignupRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Display name of the tenant organization.
             *
             * @var string $name
             *
             * @example "Acme Corp"
             */
            'name' => 'required|string|max:255',

            /**
             * URL-friendly unique slug; auto-generated from name if omitted.
             *
             * @var string $slug
             *
             * @example "acme-corp"
             */
            'slug' => 'sometimes|string|max:255|unique:tenants,slug',

            /**
             * Tenant database name; auto-generated from slug if omitted.
             *
             * @var string $database
             *
             * @example "tenant_acme_corp"
             */
            'database' => 'sometimes|string|max:255',

            /**
             * Primary domain hostname for the tenant.
             *
             * @var string $domain
             *
             * @example "acme-corp.saas.local"
             */
            'domain' => 'required|string|max:255|unique:domains,domain',

            /**
             * Plan UUID to subscribe the tenant to.
             *
             * @var string $plan_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'plan_id' => 'required|uuid|exists:plans,id',

            /**
             * Billing cycle for the initial subscription.
             *
             * @var string $billing_cycle
             *
             * @example "monthly"
             */
            'billing_cycle' => 'required|string|in:monthly,yearly',

            /**
             * Owner contact email.
             *
             * @var string $owner_email
             *
             * @example "owner@acme.com"
             */
            'owner_email' => 'required|email',

            /**
             * Owner display name.
             *
             * @var string $owner_name
             *
             * @example "John Doe"
             */
            'owner_name' => 'required|string|max:255',

            /**
             * Payment provider for checkout or trial card setup.
             *
             * @var string $payment_provider
             *
             * @example "stripe"
             */
            'payment_provider' => 'required|string|in:stripe,paystack',

            /**
             * Redirect URL after successful payment or card setup.
             *
             * @var string $success_url
             *
             * @example "https://app.example.com/signup/success"
             */
            'success_url' => 'sometimes|url',

            /**
             * Redirect URL when payment or card setup is cancelled.
             *
             * @var string $cancel_url
             *
             * @example "https://app.example.com/signup/cancel"
             */
            'cancel_url' => 'sometimes|url',

            /**
             * Optional tenant settings JSON.
             *
             * @var array<string, mixed> $settings
             *
             * @example {"theme":"light","locale":"en"}
             */
            'settings' => 'sometimes|array',

            /**
             * Optional tenant metadata JSON.
             *
             * @var array<string, mixed> $meta
             *
             * @example {"industry":"retail"}
             */
            'meta' => 'sometimes|array',
        ];
    }
}
