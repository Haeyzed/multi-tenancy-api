<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Warehouse inventory location stored in the tenant database.
 *
 * @property int $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property string $warehouse_id
 * @property int|null $zone_id
 * @property int|null $bin_id
 * @property int $quantity
 * @property int $reserved_qty
 * @property int $available_qty
 * @property int $reorder_point
 * @property int $reorder_qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InventoryLocation extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_locations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'zone_id',
        'bin_id',
        'quantity',
        'reserved_qty',
        'available_qty',
        'reorder_point',
        'reorder_qty',
    ];

    /**
     * Product at this location.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Warehouse for this inventory location.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
