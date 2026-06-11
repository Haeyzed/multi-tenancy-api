<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Product stored in the tenant catalog.
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $sku
 * @property string|null $barcode
 * @property string|null $description
 * @property string|null $short_description
 * @property int|null $brand_id
 * @property string $type
 * @property string $status
 * @property string $visibility
 * @property string $price
 * @property string|null $compare_price
 * @property string|null $cost_price
 * @property int|null $tax_class_id
 * @property string|null $weight
 * @property string|null $length
 * @property string|null $width
 * @property string|null $height
 * @property bool $requires_shipping
 * @property bool $is_featured
 * @property bool $is_gift_card
 * @property bool $allow_backorders
 * @property int $low_stock_threshold
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $canonical_url
 * @property Carbon|null $published_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Product search(?string $search)
 * @method static Builder|Product filterStatus(array $statuses)
 */
class Product extends TenantModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'products';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'short_description',
        'brand_id',
        'type',
        'status',
        'visibility',
        'price',
        'compare_price',
        'cost_price',
        'tax_class_id',
        'weight',
        'length',
        'width',
        'height',
        'requires_shipping',
        'is_featured',
        'is_gift_card',
        'allow_backorders',
        'low_stock_threshold',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'published_at',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'requires_shipping' => 'boolean',
            'is_featured' => 'boolean',
            'is_gift_card' => 'boolean',
            'allow_backorders' => 'boolean',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Brand this product belongs to.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Staff user who created this product.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Staff user who last updated this product.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to search by name, slug, or SKU.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by status values.
     *
     * @param  list<string>  $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn (Builder $q) => $q->whereIn('status', $statuses));
    }
}
