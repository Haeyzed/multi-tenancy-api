<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Customer loyalty program stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string $points_per_currency
 * @property int $min_points_redemption
 * @property string $point_value
 * @property bool $is_active
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|LoyaltyProgram search(?string $search)
 * @method static Builder|LoyaltyProgram filterIsActive(array $statuses)
 */
class LoyaltyProgram extends TenantModel
{
    use HasFactory;

    protected $table = 'loyalty_programs';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'points_per_currency',
        'min_points_redemption',
        'point_value',
        'is_active',
        'start_date',
        'end_date',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<LoyaltyProgram> $query
     * @param string|null $search
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
     * @param Builder<LoyaltyProgram> $query
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = [];

        foreach ($statuses as $status) {
            $values[] = match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            };
        }

        $values = array_values(array_unique(array_filter(
            $values,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($values !== [], fn (Builder $q): Builder => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points_per_currency' => 'decimal:2',
            'point_value' => 'decimal:2',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
