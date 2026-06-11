<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Location inventory stored in the tenant database.
 * @property int $id
 * @property string $location_id
 * @property string $product_id
 * @property string|null $variant_id
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
        'location_id',
        'product_id',
        'variant_id',
        'quantity',
        'reserved_qty',
        'reorder_point',
        'reorder_qty',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
