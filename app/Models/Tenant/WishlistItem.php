<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Wishlist items stored in the tenant database.
 *
 * @property int $id
 * @property int $wishlist_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property Carbon|null $added_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WishlistItem extends TenantModel
{
    use HasFactory;

    protected $table = 'wishlist_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'wishlist_id',
        'product_id',
        'variant_id',
        'added_at',
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
            'added_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
