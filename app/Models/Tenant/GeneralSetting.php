<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Tenant-wide general settings (single row per tenant database).
 *
 * Company identity, defaults, and policies that apply across all stores.
 *
 * @property int $id
 * @property string|null $company_name
 * @property string|null $legal_name
 * @property string|null $support_email
 * @property string|null $support_phone
 * @property string|null $support_whatsapp
 * @property string|null $billing_email
 * @property int|null $tax_id
 * @property string|null $registration_number
 * @property array<string, mixed>|null $headquarters_address
 * @property string|null $website_url
 * @property string $default_currency
 * @property string $currency_symbol
 * @property string $currency_position
 * @property string $default_timezone
 * @property string $default_language
 * @property string $default_weight_unit
 * @property string $default_dimension_unit
 * @property string|null $email_from_name
 * @property string|null $email_from_address
 * @property string|null $industry
 * @property string|null $business_type
 * @property array<string, mixed>|null $social_links
 * @property string|null $privacy_policy_url
 * @property string|null $terms_of_service_url
 * @property string|null $refund_policy_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GeneralSetting extends TenantModel
{
    use HasFactory;

    protected $table = 'general_settings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_name',
        'legal_name',
        'support_email',
        'support_phone',
        'support_whatsapp',
        'billing_email',
        'tax_id',
        'registration_number',
        'headquarters_address',
        'website_url',
        'default_currency',
        'currency_symbol',
        'currency_position',
        'default_timezone',
        'default_language',
        'default_weight_unit',
        'default_dimension_unit',
        'email_from_name',
        'email_from_address',
        'industry',
        'business_type',
        'social_links',
        'privacy_policy_url',
        'terms_of_service_url',
        'refund_policy_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'headquarters_address' => 'array',
            'social_links' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
