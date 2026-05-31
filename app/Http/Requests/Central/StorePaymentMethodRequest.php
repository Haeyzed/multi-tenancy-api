<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new tenant payment method.
 */
class StorePaymentMethodRequest extends FormRequest
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
             * UUID of the tenant that owns this payment method.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * Payment gateway that tokenized this method.
             *
             * @var string $provider
             *
             * @example "stripe"
             */
            'provider' => 'required|string|in:stripe,paddle,paypal',

            /**
             * External payment method identifier from the provider.
             *
             * @var string $provider_method_id
             *
             * @example "pm_1NxAbCdEfGhIjKlM"
             */
            'provider_method_id' => 'required|string',

            /**
             * Kind of payment method stored.
             *
             * @var string $type
             *
             * @example "card"
             */
            'type' => 'required|string|in:card,bank_account,wallet',

            /**
             * Last four digits of the card or account; nullable.
             *
             * @var string|null $last4
             *
             * @example "4242"
             */
            'last4' => 'nullable|string|size:4',

            /**
             * Card brand or bank name; nullable.
             *
             * @var string|null $brand
             *
             * @example "Visa"
             */
            'brand' => 'nullable|string|max:50',

            /**
             * Card expiration month (1–12); nullable.
             *
             * @var int|null $exp_month
             *
             * @example 12
             */
            'exp_month' => 'nullable|integer|between:1,12',

            /**
             * Card expiration year; nullable, must be 2026 or later.
             *
             * @var int|null $exp_year
             *
             * @example 2028
             */
            'exp_year' => 'nullable|integer|min:2026',

            /**
             * Whether this is the tenant's default payment method; optional.
             *
             * @var bool $is_default
             *
             * @example true
             */
            'is_default' => 'sometimes|boolean',

            /**
             * Billing address and contact details; optional.
             *
             * @var array<string, mixed> $billing_details
             *
             * @example {"name": "Jane Doe", "email": "user@example.com"}
             */
            'billing_details' => 'sometimes|array',
        ];
    }
}
