<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates updates to per-store operational settings.
 */
class UpdateStoreSettingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'primary_color' => 'sometimes|string|regex:/^#[a-fA-F0-9]{6}$/',
            'secondary_color' => 'sometimes|string|regex:/^#[a-fA-F0-9]{6}$/',
            'currency' => 'sometimes|string|size:3',
            'default_language' => 'sometimes|string|max:10',
            'timezone' => 'sometimes|string|timezone',
            'weight_unit' => 'sometimes|in:kg,g,lb,oz',
            'dimension_unit' => 'sometimes|in:cm,m,in,ft',
            'tax_included_in_prices' => 'sometimes|boolean',
            'auto_invoice' => 'sometimes|boolean',
            'order_number_prefix' => 'sometimes|string|max:20',
            'order_number_start' => 'sometimes|integer|min:1',
            'meta_title_template' => 'nullable|string|max:255',
            'meta_description_template' => 'nullable|string|max:500',
            'google_analytics_id' => 'nullable|string|max:100',
            'facebook_pixel_id' => 'nullable|string|max:100',
            'custom_scripts' => 'nullable|string',
            'maintenance_mode' => 'sometimes|boolean',
            'maintenance_message' => 'nullable|string',
            'catalog_visible' => 'sometimes|boolean',
            'checkout_enabled' => 'sometimes|boolean',
            'guest_checkout_allowed' => 'sometimes|boolean',
            'shipping_enabled' => 'sometimes|boolean',
            'cod_enabled' => 'sometimes|boolean',
            'card_payment_enabled' => 'sometimes|boolean',
        ];
    }
}
