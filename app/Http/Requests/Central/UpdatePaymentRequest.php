<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant payment record.
 */
class UpdatePaymentRequest extends BaseRequest
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
             * UUID of the tenant that made this payment; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * UUID of the invoice this payment applies to; nullable.
             *
             * @var string|null $invoice_id
             *
             * @example "770e8400-e29b-41d4-a716-446655440002"
             */
            'invoice_id' => 'nullable|integer|exists:invoices,id',

            /**
             * Payment amount in the smallest currency unit; optional on update.
             *
             * @var int $amount
             *
             * @example 9900
             */
            'amount' => 'sometimes|integer|min:0',

            /**
             * ISO 4217 three-letter currency code; optional on update.
             *
             * @var string $currency
             *
             * @example "USD"
             */
            'currency' => 'sometimes|string|size:3',

            /**
             * Current status of the payment transaction; optional on update.
             *
             * @var string $status
             *
             * @example "refunded"
             */
            'status' => 'sometimes|string|in:pending,succeeded,failed,refunded',

            /**
             * Payment gateway used to process this transaction; optional on update.
             *
             * @var string $payment_provider
             *
             * @example "stripe"
             */
            'payment_provider' => 'sometimes|string|in:stripe,paddle,paypal',

            /**
             * External payment identifier from the provider; nullable.
             *
             * @var string|null $provider_payment_id
             *
             * @example "ch_3NxAbCdEfGhIjKlM"
             */
            'provider_payment_id' => 'nullable|string',

            /**
             * Type of payment method used; nullable.
             *
             * @var string|null $payment_method_type
             *
             * @example "card"
             */
            'payment_method_type' => 'nullable|string|in:card,bank_transfer,wallet,crypto',

            /**
             * Last four digits of the payment method; nullable.
             *
             * @var string|null $payment_method_last4
             *
             * @example "4242"
             */
            'payment_method_last4' => 'nullable|string|size:4',

            /**
             * Reason for payment failure; nullable.
             *
             * @var string|null $failure_message
             *
             * @example "Insufficient funds"
             */
            'failure_message' => 'nullable|string',

            /**
             * Amount refunded in the smallest currency unit; optional.
             *
             * @var int $refunded_amount
             *
             * @example 5000
             */
            'refunded_amount' => 'sometimes|integer|min:0',
        ];
    }
}
