<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Inventory adjustment stored in the tenant database.
 *
 * @property string $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property string $warehouse_id
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
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $table = 'inventory_adjustments';
    protected $keyType = 'string';

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
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Warehouse where the adjustment occurred.
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
