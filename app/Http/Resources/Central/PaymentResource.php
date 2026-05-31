<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * Identifier of the paying tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Identifier of the related invoice.
             *
             * @example 100
             *
             * @default null
             */
            'invoice_id' => $this->invoice_id,

            /**
             * Payment amount in the payment currency.
             *
             * @example "29.99"
             */
            'amount' => $this->amount,

            /**
             * ISO 4217 currency code.
             *
             * @example "USD"
             */
            'currency' => $this->currency,

            /**
             * Current payment status.
             *
             * @example "succeeded"
             */
            'status' => $this->status,

            /**
             * External payment provider name.
             *
             * @example "stripe"
             */
            'payment_provider' => $this->payment_provider,

            /**
             * External payment identifier at the provider.
             *
             * @example "pi_1234567890"
             *
             * @default null
             */
            'provider_payment_id' => $this->provider_payment_id,

            /**
             * Type of payment method used.
             *
             * @example "card"
             *
             * @default null
             */
            'payment_method_type' => $this->payment_method_type,

            /**
             * Last four digits of the payment method used.
             *
             * @example "4242"
             *
             * @default null
             */
            'payment_method_last4' => $this->payment_method_last4,

            /**
             * Failure message if the payment did not succeed.
             *
             * @example "Insufficient funds"
             *
             * @default null
             */
            'failure_message' => $this->failure_message,

            /**
             * Amount refunded against this payment.
             *
             * @example "0.00"
             *
             * @default "0.00"
             */
            'refunded_amount' => $this->refunded_amount,

            /**
             * Timestamp when the payment was created.
             *
             * @example "2026-05-02T09:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the payment was last updated.
             *
             * @example "2026-05-02T09:05:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Paying tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),

            /**
             * Related invoice when eager loaded.
             *
             * @default null
             */
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
        ];
    }
}
