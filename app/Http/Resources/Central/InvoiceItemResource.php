<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InvoiceItem
 */
class InvoiceItemResource extends JsonResource
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
             * Identifier of the parent invoice.
             *
             * @example 100
             */
            'invoice_id' => $this->invoice_id,

            /**
             * Line item description.
             *
             * @example "Professional plan — May 2026"
             */
            'description' => $this->description,

            /**
             * Quantity billed.
             *
             * @example 1
             *
             * @default 1
             */
            'quantity' => $this->quantity,

            /**
             * Price per unit before tax.
             *
             * @example "29.99"
             */
            'unit_amount' => $this->unit_amount,

            /**
             * Total line amount before tax.
             *
             * @example "29.99"
             */
            'amount' => $this->amount,

            /**
             * Identifier of the related plan, if any.
             *
             * @example 2
             *
             * @default null
             */
            'plan_id' => $this->plan_id,

            /**
             * Start of the service period covered by the line item.
             *
             * @example "2026-05-01T00:00:00+00:00"
             *
             * @default null
             */
            'period_start' => $this->period_start?->toIso8601String(),

            /**
             * End of the service period covered by the line item.
             *
             * @example "2026-06-01T00:00:00+00:00"
             *
             * @default null
             */
            'period_end' => $this->period_end?->toIso8601String(),

            /**
             * Timestamp when the line item was created.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the line item was last updated.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Parent invoice when eager loaded.
             *
             * @default null
             */
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),

            /**
             * Related plan when eager loaded.
             *
             * @default null
             */
            'plan' => new PlanResource($this->whenLoaded('plan')),
        ];
    }
}
