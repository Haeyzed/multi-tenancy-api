<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new tenant invoice.
 */
class StoreInvoiceRequest extends BaseRequest
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
             * UUID of the tenant being invoiced.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * UUID of the related subscription; nullable for one-off invoices.
             *
             * @var string|null $subscription_id
             *
             * @example "660e8400-e29b-41d4-a716-446655440001"
             */
            'subscription_id' => 'nullable|uuid|exists:subscriptions,id',

            /**
             * Unique invoice number identifier.
             *
             * @var string $invoice_number
             *
             * @example "INV-2026-00042"
             */
            'invoice_number' => 'required|string|unique:invoices,invoice_number',

            /**
             * Current lifecycle status of the invoice.
             *
             * @var string $status
             *
             * @example "open"
             */
            'status' => 'required|string|in:draft,open,paid,void,uncollectible',

            /**
             * Total amount due in the smallest currency unit (e.g. cents).
             *
             * @var int $amount_due
             *
             * @example 9900
             */
            'amount_due' => 'required|integer|min:0',

            /**
             * Amount already paid in the smallest currency unit; optional.
             *
             * @var int $amount_paid
             *
             * @example 0
             */
            'amount_paid' => 'sometimes|integer|min:0',

            /**
             * Remaining balance in the smallest currency unit.
             *
             * @var int $amount_remaining
             *
             * @example 9900
             */
            'amount_remaining' => 'required|integer|min:0',

            /**
             * ISO 4217 three-letter currency code.
             *
             * @var string $currency
             *
             * @example "USD"
             */
            'currency' => 'required|string|size:3',

            /**
             * Start of the billing period covered by this invoice.
             *
             * @var string $billing_period_start
             *
             * @example "2026-01-01T00:00:00Z"
             */
            'billing_period_start' => 'required|date',

            /**
             * End of the billing period covered by this invoice.
             *
             * @var string $billing_period_end
             *
             * @example "2026-01-31T23:59:59Z"
             */
            'billing_period_end' => 'required|date',

            /**
             * Date by which payment is expected.
             *
             * @var string $due_date
             *
             * @example "2026-02-15T00:00:00Z"
             */
            'due_date' => 'required|date',

            /**
             * Timestamp when the invoice was fully paid; nullable.
             *
             * @var string|null $paid_at
             *
             * @example "2026-02-10T14:22:00Z"
             */
            'paid_at' => 'nullable|date',

            /**
             * URL to the downloadable PDF invoice; nullable.
             *
             * @var string|null $pdf_url
             *
             * @example "https://cdn.example.com/invoices/INV-2026-00042.pdf"
             */
            'pdf_url' => 'nullable|url',

            /**
             * External payment provider intent identifier; nullable.
             *
             * @var string|null $payment_intent_id
             *
             * @example "pi_3NxAbCdEfGhIjKlM"
             */
            'payment_intent_id' => 'nullable|string',

            /**
             * Snapshot of line items included on the invoice; optional.
             *
             * @var array<int, array<string, mixed>> $line_items
             *
             * @example [{"description": "Pro Plan", "amount": 9900}]
             */
            'line_items' => 'sometimes|array',

            /**
             * Internal or customer-facing notes; nullable.
             *
             * @var string|null $notes
             *
             * @example "Annual renewal invoice"
             */
            'notes' => 'nullable|string',
        ];
    }
}
