<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Per-store operational and storefront settings.
 *
 * Linked 1:1 to a {@see Store}. Tenant-wide defaults live in {@see GeneralSetting}.
 *
 * @property int $id
 * @property string $store_id
 * @property string|null $primary_color
 * @property string|null $secondary_color
 * @property string $currency
 * @property string $default_language
 * @property string $timezone
 * @property string $weight_unit
 * @property string $dimension_unit
 * @property bool $tax_included_in_prices
 * @property bool $auto_invoice
 * @property string|null $order_number_prefix
 * @property int $order_number_start
 * @property string|null $meta_title_template
 * @property string|null $meta_description_template
 * @property string|null $google_analytics_id
 * @property string|null $facebook_pixel_id
 * @property string|null $custom_scripts
 * @property bool $maintenance_mode
 * @property string|null $maintenance_message
 * @property bool $catalog_visible
 * @property bool $checkout_enabled
 * @property bool $guest_checkout_allowed
 * @property bool $shipping_enabled
 * @property bool $cod_enabled
 * @property bool $card_payment_enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class StoreSetting extends TenantModel
{
    use HasFactory;

    protected $table = 'store_settings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'primary_color',
        'secondary_color',
        'currency',
        'default_language',
        'timezone',
        'weight_unit',
        'dimension_unit',
        'tax_included_in_prices',
        'auto_invoice',
        'order_number_prefix',
        'order_number_start',
        'meta_title_template',
        'meta_description_template',
        'google_analytics_id',
        'facebook_pixel_id',
        'custom_scripts',
        'maintenance_mode',
        'maintenance_message',
        'catalog_visible',
        'checkout_enabled',
        'guest_checkout_allowed',
        'shipping_enabled',
        'cod_enabled',
        'card_payment_enabled',
    ];

    /**
     * Store this settings row belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tax_included_in_prices' => 'boolean',
            'auto_invoice' => 'boolean',
            'maintenance_mode' => 'boolean',
            'catalog_visible' => 'boolean',
            'checkout_enabled' => 'boolean',
            'guest_checkout_allowed' => 'boolean',
            'shipping_enabled' => 'boolean',
            'cod_enabled' => 'boolean',
            'card_payment_enabled' => 'boolean',
            'order_number_start' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
