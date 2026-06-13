<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pos transaction items stored in the tenant database.
 *
 * @property int $id
 * @property int $transaction_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $quantity
 * @property string $unit_price
 * @property string $line_total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PosTransactionItem extends TenantModel
{
    use HasFactory;

    protected $table = 'pos_transaction_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'transaction_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'line_total',
    ];

    /**
     * Related Product.
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
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
