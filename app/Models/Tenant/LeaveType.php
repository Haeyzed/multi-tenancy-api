<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Leave type stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $color
 * @property bool $is_paid
 * @property int $default_days_per_year
 * @property bool $is_carry_forward
 * @property int $max_carry_days
 * @property bool $requires_approval
 * @property int $min_notice_days
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|LeaveType search(?string $search)
 * @method static Builder|LeaveType filterIsActive(array $statuses)
 */
class LeaveType extends TenantModel
{
    use HasFactory;

    protected $table = 'leave_types';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_paid',
        'default_days_per_year',
        'is_carry_forward',
        'max_carry_days',
        'requires_approval',
        'min_notice_days',
        'is_active',
        'sort_order',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<LeaveType> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<LeaveType> $query
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
            'is_paid' => 'boolean',
            'is_carry_forward' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
