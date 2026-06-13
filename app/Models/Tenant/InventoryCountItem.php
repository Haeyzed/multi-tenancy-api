<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Inventory count line item stored in the tenant database.
 *
 * @property int $id
 * @property int $count_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $expected_qty
 * @property int $counted_qty
 * @property int $difference
 * @property string|null $reason
 * @property int|null $bin_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InventoryCountItem extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_count_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'count_id',
        'product_id',
        'variant_id',
        'expected_qty',
        'counted_qty',
        'difference',
        'reason',
        'bin_id',
    ];

    /**
     * Product being counted.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
