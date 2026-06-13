<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Tax rates stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $country
 * @property string|null $state
 * @property string|null $postal_code_pattern
 * @property string $rate_percent
 * @property bool $is_compound
 * @property bool $is_active
 * @property int $priority
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|TaxRate search(?string $search)
 * @method static Builder|TaxRate filterIsActive(array $statuses)
 */
class TaxRate extends TenantModel
{
    use HasFactory;

    protected $table = 'tax_rates';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'country',
        'state',
        'postal_code_pattern',
        'rate_percent',
        'is_compound',
        'is_active',
        'priority',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<TaxRate> $query
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
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<TaxRate> $query
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
            'rate_percent' => 'decimal:2',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
