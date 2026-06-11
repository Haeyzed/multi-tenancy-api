<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Cart line item stored in the tenant database.
 *
 * @property int $id
 * @property string $cart_id
 * @property string $product_id
 * @property string|null $variant_id
 * @property int $quantity
 * @property string $unit_price
 * @property string|null $unit_compare_price
 * @property string $line_total
 * @property string $line_discount
 * @property string $tax_amount
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CartItem extends TenantModel
{
    use HasFactory;

    protected $table = 'cart_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'unit_compare_price',
        'line_total',
        'line_discount',
        'tax_amount',
        'metadata',
    ];

    /**
     * Cart this line item belongs to.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Product in this cart line.
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
            'unit_compare_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
