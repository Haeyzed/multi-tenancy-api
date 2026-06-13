<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product reviews stored in the tenant database.
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int $rating
 * @property string|null $title
 * @property string|null $body
 * @property bool $is_verified_purchase
 * @property bool $is_approved
 * @property int $helpful_count
 * @property array<string, mixed>|null $images
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|ProductReview search(?string $search)
 */
class ProductReview extends TenantModel
{
    use HasFactory;

    protected $table = 'product_reviews';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'body',
        'is_verified_purchase',
        'is_approved',
        'helpful_count',
        'images',
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
     * Related User.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Related Order.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<ProductReview> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified_purchase' => 'boolean',
            'is_approved' => 'boolean',
            'images' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
