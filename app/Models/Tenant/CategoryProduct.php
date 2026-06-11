<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Category product stored in the tenant database.
 * @property int $id
 * @property string $category_id
 * @property string $product_id
 * @property bool $is_primary
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CategoryProduct extends TenantModel
{
    use HasFactory;

    protected $table = 'category_product';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'product_id',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Related Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
