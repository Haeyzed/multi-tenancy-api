<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Order items stored in the tenant database.
 * @property int $id
 * @property string $order_id
 * @property string|null $product_id
 * @property string|null $variant_id
 * @property array<string, mixed>|null $product_snapshot
 * @property array<string, mixed>|null $variant_snapshot
 * @property string|null $sku
 * @property string|null $name
 * @property int $quantity
 * @property string $unit_price
 * @property string|null $unit_compare_price
 * @property string $line_total
 * @property string $line_discount
 * @property string $tax_amount
 * @property string $tax_rate
 * @property string|null $weight
 * @property string $fulfillment_status
 * @property int $fulfilled_quantity
 * @property int $returned_quantity
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|OrderItem search(?string $search)
 */
class OrderItem extends TenantModel
{
    use HasFactory;

    protected $table = 'order_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_snapshot',
        'variant_snapshot',
        'sku',
        'name',
        'quantity',
        'unit_price',
        'unit_compare_price',
        'line_total',
        'line_discount',
        'tax_amount',
        'tax_rate',
        'weight',
        'fulfillment_status',
        'fulfilled_quantity',
        'returned_quantity',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'product_snapshot' => 'array',
            'variant_snapshot' => 'array',
            'unit_price' => 'decimal:2',
            'unit_compare_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'line_discount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'weight' => 'decimal:2',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Related Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
            );
        });
    }
}
