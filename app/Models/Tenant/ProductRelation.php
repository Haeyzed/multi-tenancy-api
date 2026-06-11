<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product relations stored in the tenant database.
 *
 * @property int $id
 * @property string $product_id
 * @property string $related_product_id
 * @property string $type
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ProductRelation extends TenantModel
{
    use HasFactory;

    protected $table = 'product_relations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'related_product_id',
        'type',
        'sort_order',
    ];

    /**
     * Related Product.
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
