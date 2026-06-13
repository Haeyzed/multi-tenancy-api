<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Company holiday stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property Carbon|null $date
 * @property string $type
 * @property bool $is_recurring
 * @property string|null $recurrence_pattern
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Holiday search(?string $search)
 * @method static Builder|Holiday filterIsActive(array $statuses)
 */
class Holiday extends TenantModel
{
    use HasFactory;

    protected $table = 'holidays';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'date',
        'type',
        'is_recurring',
        'recurrence_pattern',
        'is_active',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<Holiday> $query
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
     * @param Builder<Holiday> $query
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
            'date' => 'date',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
