<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Per-store inventory levels stored in the tenant database.
 *
 * @property int $id
 * @property int $store_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $quantity
 * @property int $reserved_qty
 * @property int $reorder_point
 * @property int $reorder_qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LocationInventory extends TenantModel
{
    use HasFactory;

    protected $table = 'location_inventory';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'store_id',
        'product_id',
        'variant_id',
        'quantity',
        'reserved_qty',
        'reorder_point',
        'reorder_qty',
    ];

    /**
     * Store this inventory belongs to.
     *
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Product at this store.
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
