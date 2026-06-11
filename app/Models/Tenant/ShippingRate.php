<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Shipping rates stored in the tenant database.
 * @property int $id
 * @property int $shipping_zone_id
 * @property string|null $name
 * @property string $based_on
 * @property array<string, mixed>|null $conditions
 * @property string $price
 * @property bool $is_free_shipping_above
 * @property string|null $free_above_amount
 * @property int|null $delivery_days_min
 * @property int|null $delivery_days_max
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ShippingRate search(?string $search)
 */
class ShippingRate extends TenantModel
{
    use HasFactory;

    protected $table = 'shipping_rates';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shipping_zone_id',
        'name',
        'based_on',
        'conditions',
        'price',
        'is_free_shipping_above',
        'free_above_amount',
        'delivery_days_min',
        'delivery_days_max',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'price' => 'decimal:2',
            'is_free_shipping_above' => 'boolean',
            'free_above_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related ShippingZone.
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
            );
        });
    }
}
