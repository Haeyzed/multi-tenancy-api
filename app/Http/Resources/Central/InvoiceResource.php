<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
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
             * Identifier of the billed tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Identifier of the related subscription.
             *
             * @example 10
             *
             * @default null
             */
            'subscription_id' => $this->subscription_id,

            /**
             * Human-readable invoice number.
             *
             * @example "INV-2026-0001"
             */
            'invoice_number' => $this->invoice_number,

            /**
             * Current invoice status.
             *
             * @example "paid"
             */
            'status' => $this->status,

            /**
             * Total amount due in the invoice currency.
             *
             * @example "29.99"
             */
            'amount_due' => $this->amount_due,

            /**
             * Amount already paid.
             *
             * @example "29.99"
             *
             * @default "0.00"
             */
            'amount_paid' => $this->amount_paid,

            /**
             * Outstanding balance remaining.
             *
             * @example "0.00"
             *
             * @default "0.00"
             */
            'amount_remaining' => $this->amount_remaining,

            /**
             * ISO 4217 currency code.
             *
             * @example "USD"
             */
            'currency' => $this->currency,

            /**
             * Start of the billing period covered by the invoice.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'billing_period_start' => $this->billing_period_start?->toIso8601String(),

            /**
             * End of the billing period covered by the invoice.
             *
             * @example "2026-06-01T00:00:00+00:00"
             */
            'billing_period_end' => $this->billing_period_end?->toIso8601String(),

            /**
             * Payment due date.
             *
             * @example "2026-06-15T00:00:00+00:00"
             *
             * @default null
             */
            'due_date' => $this->due_date?->toIso8601String(),

            /**
             * Timestamp when the invoice was fully paid.
             *
             * @example "2026-05-02T09:00:00+00:00"
             *
             * @default null
             */
            'paid_at' => $this->paid_at?->toIso8601String(),

            /**
             * URL to the downloadable PDF invoice.
             *
             * @example "https://example.com/invoices/1.pdf"
             *
             * @default null
             */
            'pdf_url' => $this->pdf_url,

            /**
             * External payment intent identifier.
             *
             * @example "pi_1234567890"
             *
             * @default null
             */
            'payment_intent_id' => $this->payment_intent_id,

            /**
             * Denormalized line item snapshot.
             *
             * @example [{"description":"Professional plan","amount":"29.99"}]
             *
             * @default null
             */
            'line_items' => $this->line_items,

            /**
             * Internal notes on the invoice.
             *
             * @example "Annual renewal discount applied"
             *
             * @default null
             */
            'notes' => $this->notes,

            /**
             * Timestamp when the invoice was created.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the invoice was last updated.
             *
             * @example "2026-05-02T09:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Billed tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),

            /**
             * Related subscription when eager loaded.
             *
             * @default null
             */
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),

            /**
             * Invoice line items when eager loaded.
             *
             * @default null
             */
            'invoice_items' => InvoiceItemResource::collection($this->whenLoaded('invoiceItems')),

            /**
             * Payments applied to the invoice when eager loaded.
             *
             * @default null
             */
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            /**
             * Subscription referencing this invoice as latest when eager loaded.
             *
             * @default null
             */
            'subscription_as_latest_invoice' => new SubscriptionResource($this->whenLoaded('subscriptionAsLatestInvoice')),
        ];
    }
}
