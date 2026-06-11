<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Product variants stored in the tenant database.
 * @property string $id
 * @property string $product_id
 * @property string|null $sku
 * @property string|null $barcode
 * @property string|null $variant_name
 * @property string $price_adjustment
 * @property string $weight_adjustment
 * @property bool $is_default
 * @property bool $is_active
 * @property int $stock_quantity
 * @property int $low_stock_threshold
 * @property array<string, mixed>|null $option_values
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ProductVariant search(?string $search)
 */
class ProductVariant extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'product_variants';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'variant_name',
        'price_adjustment',
        'weight_adjustment',
        'is_default',
        'is_active',
        'stock_quantity',
        'low_stock_threshold',
        'option_values',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'weight_adjustment' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'option_values' => 'array',
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

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('sku', 'like', "%{$search}%")
            );
        });
    }
}
