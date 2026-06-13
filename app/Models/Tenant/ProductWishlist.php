<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Product wishlists stored in the tenant database.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property bool $is_default
 * @property string|null $share_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|ProductWishlist search(?string $search)
 */
class ProductWishlist extends TenantModel
{
    use HasFactory;

    protected $table = 'product_wishlists';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'is_default',
        'share_token',
    ];

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
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<ProductWishlist> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%");
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
            'is_default' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
