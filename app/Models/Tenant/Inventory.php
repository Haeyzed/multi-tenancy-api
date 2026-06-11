<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product inventory level stored in the tenant database.
 *
 * @property int $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property int|null $location_id
 * @property int $quantity
 * @property int $reserved_quantity
 * @property int $available_quantity
 * @property int $reorder_point
 * @property int $reorder_qty
 * @property Carbon|null $last_counted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Inventory extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'location_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'reorder_point',
        'reorder_qty',
        'last_counted_at',
    ];

    /**
     * Product being tracked.
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
            'last_counted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
