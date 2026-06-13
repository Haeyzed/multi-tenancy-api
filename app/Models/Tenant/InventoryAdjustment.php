<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Inventory adjustment stored in the tenant database.
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $warehouse_id
 * @property string $type
 * @property int $quantity
 * @property string|null $reason
 * @property string $approved_by
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InventoryAdjustment extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_adjustments';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'type',
        'quantity',
        'reason',
        'approved_by',
        'created_by',
    ];

    /**
     * Product being adjusted.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Warehouse where the adjustment occurred.
     *
     * @return BelongsTo<Warehouse, $this>
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
