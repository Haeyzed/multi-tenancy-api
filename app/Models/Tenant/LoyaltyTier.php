<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Loyalty program tier stored in the tenant database.
 *
 * @property int $id
 * @property string $program_id
 * @property string|null $name
 * @property int $min_points
 * @property int|null $max_points
 * @property string $discount_percent
 * @property array<string, mixed>|null $benefits
 * @property string|null $color
 * @property int|null $icon_media_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|LoyaltyTier search(?string $search)
 * @method static Builder|LoyaltyTier filterIsActive(array $statuses)
 */
class LoyaltyTier extends TenantModel
{
    use HasFactory;

    protected $table = 'loyalty_tiers';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'name',
        'min_points',
        'max_points',
        'discount_percent',
        'benefits',
        'color',
        'icon_media_id',
        'is_active',
    ];

    /**
     * Scope a query to search by name.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
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
            'discount_percent' => 'decimal:2',
            'benefits' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
