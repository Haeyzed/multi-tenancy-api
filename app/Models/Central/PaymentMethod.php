<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\PaymentMethodKind;
use App\Enums\Central\PaymentProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stored payment method for a tenant.
 *
 * @property int $id
 * @property string $tenant_id
 * @property PaymentProvider $provider
 * @property string $provider_method_id
 * @property PaymentMethodKind $type
 * @property string|null $last4
 * @property string|null $brand
 * @property int|null $exp_month
 * @property int|null $exp_year
 * @property bool $is_default
 * @property array<string, mixed>|null $billing_details
 */
class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'provider',
        'provider_method_id',
        'type',
        'last4',
        'brand',
        'exp_month',
        'exp_year',
        'is_default',
        'billing_details',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'type' => PaymentMethodKind::class,
            'billing_details' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Tenant that owns this payment method.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
