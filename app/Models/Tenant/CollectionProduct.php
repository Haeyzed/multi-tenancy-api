<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Collection-to-product pivot stored in the tenant database.
 *
 * @property int $id
 * @property int $collection_id
 * @property int $product_id
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CollectionProduct extends TenantModel
{
    use HasFactory;

    protected $table = 'collection_product';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'collection_id',
        'product_id',
        'sort_order',
    ];

    /**
     * Collection in this assignment.
     *
     * @return BelongsTo<Collection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Product in this assignment.
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
