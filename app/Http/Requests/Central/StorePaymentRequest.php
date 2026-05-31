<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new tenant payment record.
 */
class StorePaymentRequest extends FormRequest
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
             * UUID of the tenant that made this payment.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * UUID of the invoice this payment applies to; nullable.
             *
             * @var string|null $invoice_id
             *
             * @example "770e8400-e29b-41d4-a716-446655440002"
             */
            'invoice_id' => 'nullable|uuid|exists:invoices,id',

            /**
             * Payment amount in the smallest currency unit.
             *
             * @var int $amount
             *
             * @example 9900
             */
            'amount' => 'required|integer|min:0',

            /**
             * ISO 4217 three-letter currency code.
             *
             * @var string $currency
             *
             * @example "USD"
             */
            'currency' => 'required|string|size:3',

            /**
             * Current status of the payment transaction.
             *
             * @var string $status
             *
             * @example "succeeded"
             */
            'status' => 'required|string|in:pending,succeeded,failed,refunded',

            /**
             * Payment gateway used to process this transaction.
             *
             * @var string $payment_provider
             *
             * @example "stripe"
             */
            'payment_provider' => 'required|string|in:stripe,paddle,paypal',

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
             * @example 0
             */
            'refunded_amount' => 'sometimes|integer|min:0',
        ];
    }
}
