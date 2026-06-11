<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Tax brackets stored in the tenant database.
 *
 * @property int $id
 * @property string|null $country
 * @property string|null $region
 * @property string $min_income
 * @property string|null $max_income
 * @property string $rate
 * @property bool $is_percentage
 * @property string $fixed_amount
 * @property Carbon|null $effective_date
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TaxBracket extends TenantModel
{
    use HasFactory;

    protected $table = 'tax_brackets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'country',
        'region',
        'min_income',
        'max_income',
        'rate',
        'is_percentage',
        'fixed_amount',
        'effective_date',
        'is_active',
    ];

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
            'min_income' => 'decimal:2',
            'max_income' => 'decimal:2',
            'rate' => 'decimal:2',
            'is_percentage' => 'boolean',
            'fixed_amount' => 'decimal:2',
            'effective_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
