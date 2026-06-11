<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Wishlist items stored in the tenant database.
 * @property int $id
 * @property string $wishlist_id
 * @property string $product_id
 * @property string|null $variant_id
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

    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
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
}
