<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Supplier products stored in the tenant database.
 *
 * @property int $id
 * @property string $supplier_id
 * @property string|null $product_id
 * @property string|null $sku
 * @property string|null $supplier_sku
 * @property string $cost_price
 * @property int $min_order_qty
 * @property int $lead_time_days
 * @property bool $is_preferred
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SupplierProduct search(?string $search)
 * @method static Builder|SupplierProduct filterIsActive(array $statuses)
 */
class SupplierProduct extends TenantModel
{
    use HasFactory;

    protected $table = 'supplier_products';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'supplier_id',
        'product_id',
        'sku',
        'supplier_sku',
        'cost_price',
        'min_order_qty',
        'lead_time_days',
        'is_preferred',
        'is_active',
    ];

    /**
     * Related Supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
                $inner->where('sku', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = QueryFilter::booleanStatuses($statuses);

        $query->when($values !== [], fn(Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */


    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'is_preferred' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
