<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaymentMethod
 */
class PaymentMethodResource extends JsonResource
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
             * Identifier of the owning tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Payment provider name.
             *
             * @example "stripe"
             */
            'provider' => $this->provider,

            /**
             * External payment method identifier at the provider.
             *
             * @example "pm_1234567890"
             */
            'provider_method_id' => $this->provider_method_id,

            /**
             * Payment method type.
             *
             * @example "card"
             */
            'type' => $this->type,

            /**
             * Last four digits of the payment method.
             *
             * @example "4242"
             *
             * @default null
             */
            'last4' => $this->last4,

            /**
             * Card brand or payment method brand.
             *
             * @example "visa"
             *
             * @default null
             */
            'brand' => $this->brand,

            /**
             * Card expiration month.
             *
             * @example 12
             *
             * @default null
             */
            'exp_month' => $this->exp_month,

            /**
             * Card expiration year.
             *
             * @example 2028
             *
             * @default null
             */
            'exp_year' => $this->exp_year,

            /**
             * Whether this is the tenant's default payment method.
             *
             * @example true
             *
             * @default false
             */
            'is_default' => (bool) $this->is_default,

            /**
             * Billing details associated with the payment method.
             *
             * @example {"name":"John Doe","email":"john@example.com"}
             *
             * @default null
             */
            'billing_details' => $this->billing_details,

            /**
             * Timestamp when the payment method was created.
             *
             * @example "2026-01-01T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the payment method was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Owning tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
