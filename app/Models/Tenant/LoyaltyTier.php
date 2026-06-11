<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Loyalty tiers stored in the tenant database.
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

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
            );
        });
    }
}
