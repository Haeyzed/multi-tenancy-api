<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Store settings stored in the tenant database.
 *
 * @property int $id
 * @property string|null $store_name
 * @property string|null $store_slug
 * @property string|null $tagline
 * @property string|null $description
 * @property int|null $logo_media_id
 * @property int|null $favicon_media_id
 * @property string|null $primary_color
 * @property string|null $secondary_color
 * @property string|null $currency
 * @property string|null $default_language
 * @property string|null $timezone
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
        'store_name',
        'store_slug',
        'tagline',
        'description',
        'logo_media_id',
        'favicon_media_id',
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
    ];

    protected function casts(): array
    {
        return [
            'tax_included_in_prices' => 'boolean',
            'auto_invoice' => 'boolean',
            'maintenance_mode' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
