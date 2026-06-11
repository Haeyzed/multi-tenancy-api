<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product price rules stored in the tenant database.
 * @property int $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property string $type
 * @property string $value
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int $min_qty
 * @property int|null $max_qty
 * @property int|null $customer_group_id
 * @property int $priority
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProductPriceRule extends TenantModel
{
    use HasFactory;

    protected $table = 'product_price_rules';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'type',
        'value',
        'start_date',
        'end_date',
        'min_qty',
        'max_qty',
        'customer_group_id',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
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
