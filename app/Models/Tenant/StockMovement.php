<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Stock movements stored in the tenant database.
 *
 * @property int $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property int|null $location_id
 * @property string $type
 * @property int $quantity
 * @property int $before_quantity
 * @property int $after_quantity
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $reason
 * @property string|null $performed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class StockMovement extends TenantModel
{
    use HasFactory;

    protected $table = 'stock_movements';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'location_id',
        'type',
        'quantity',
        'before_quantity',
        'after_quantity',
        'reference_type',
        'reference_id',
        'reason',
        'performed_by',
    ];

    /**
     * Related Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
