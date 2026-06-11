<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product option combinations stored in the tenant database.
 * @property int $id
 * @property string $product_id
 * @property string|null $combination_key
 * @property string|null $sku
 * @property string|null $price
 * @property int $stock
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ProductOptionCombination search(?string $search)
 */
class ProductOptionCombination extends TenantModel
{
    use HasFactory;

    protected $table = 'product_option_combinations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'combination_key',
        'sku',
        'price',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
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
