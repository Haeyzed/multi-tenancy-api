<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee benefit enrollment stored in the tenant database.
 *
 * @property int $id
 * @property int $employee_id
 * @property string $benefit_type
 * @property string|null $provider
 * @property string|null $policy_number
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $premium_amount
 * @property string $employee_contribution
 * @property string $employer_contribution
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|EmployeeBenefit filterIsActive(array $statuses)
 */
class EmployeeBenefit extends TenantModel
{
    use HasFactory;

    protected $table = 'employee_benefits';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'benefit_type',
        'provider',
        'policy_number',
        'start_date',
        'end_date',
        'premium_amount',
        'employee_contribution',
        'employer_contribution',
        'is_active',
    ];

    /**
     * Employee this benefit belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<EmployeeBenefit> $query
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
            'start_date' => 'date',
            'end_date' => 'date',
            'premium_amount' => 'decimal:2',
            'employee_contribution' => 'decimal:2',
            'employer_contribution' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
