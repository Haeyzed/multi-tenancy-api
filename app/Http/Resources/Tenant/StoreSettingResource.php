<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StoreSetting
 */
class StoreSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            /** @example "#3B82F6" */
            'primary_color' => $this->primary_color,
            /** @example "#10B981" */
            'secondary_color' => $this->secondary_color,
            /** @example "USD" */
            'currency' => $this->currency,
            'default_language' => $this->default_language,
            'timezone' => $this->timezone,
            'weight_unit' => $this->weight_unit,
            'dimension_unit' => $this->dimension_unit,
            'tax_included_in_prices' => (bool) $this->tax_included_in_prices,
            'auto_invoice' => (bool) $this->auto_invoice,
            'order_number_prefix' => $this->order_number_prefix,
            'order_number_start' => $this->order_number_start,
            'meta_title_template' => $this->meta_title_template,
            'meta_description_template' => $this->meta_description_template,
            'google_analytics_id' => $this->google_analytics_id,
            'facebook_pixel_id' => $this->facebook_pixel_id,
            'custom_scripts' => $this->custom_scripts,
            'maintenance_mode' => (bool) $this->maintenance_mode,
            'maintenance_message' => $this->maintenance_message,
            'catalog_visible' => (bool) $this->catalog_visible,
            'checkout_enabled' => (bool) $this->checkout_enabled,
            'guest_checkout_allowed' => (bool) $this->guest_checkout_allowed,
            'shipping_enabled' => (bool) $this->shipping_enabled,
            'cod_enabled' => (bool) $this->cod_enabled,
            'card_payment_enabled' => (bool) $this->card_payment_enabled,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
