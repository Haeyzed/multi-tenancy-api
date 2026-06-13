<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new invoice line item.
 */
class StoreInvoiceItemRequest extends BaseRequest
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
             * UUID of the parent invoice.
             *
             * @var string $invoice_id
             *
             * @example "770e8400-e29b-41d4-a716-446655440002"
             */
            'invoice_id' => 'required|integer|exists:invoices,id',

            /**
             * Description of the billed item or service.
             *
             * @var string $description
             *
             * @example "Pro Plan - Monthly"
             */
            'description' => 'required|string|max:255',

            /**
             * Number of units billed.
             *
             * @var int $quantity
             *
             * @example 1
             */
            'quantity' => 'required|integer|min:1',

            /**
             * Price per unit in the smallest currency unit.
             *
             * @var int $unit_amount
             *
             * @example 9900
             */
            'unit_amount' => 'required|integer',

            /**
             * Total line amount in the smallest currency unit.
             *
             * @var int $amount
             *
             * @example 9900
             */
            'amount' => 'required|integer',

            /**
             * UUID of the associated plan; nullable for custom charges.
             *
             * @var string|null $plan_id
             *
             * @example "880e8400-e29b-41d4-a716-446655440003"
             */
            'plan_id' => 'nullable|integer|exists:plans,id',

            /**
             * Start of the service period for this line item; nullable.
             *
             * @var string|null $period_start
             *
             * @example "2026-01-01T00:00:00Z"
             */
            'period_start' => 'nullable|date',

            /**
             * End of the service period for this line item; nullable.
             *
             * @var string|null $period_end
             *
             * @example "2026-01-31T23:59:59Z"
             */
            'period_end' => 'nullable|date',
        ];
    }
}
