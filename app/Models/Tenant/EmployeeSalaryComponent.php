<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee salary component assignment stored in the tenant database.
 *
 * @property int $id
 * @property string $employee_id
 * @property int $component_id
 * @property string $amount
 * @property bool $is_percentage
 * @property string|null $percentage_of
 * @property Carbon|null $effective_date
 * @property Carbon|null $end_date
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|EmployeeSalaryComponent filterIsActive(array $statuses)
 */
class EmployeeSalaryComponent extends TenantModel
{
    use HasFactory;

    protected $table = 'employee_salary_components';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'component_id',
        'amount',
        'is_percentage',
        'percentage_of',
        'effective_date',
        'end_date',
        'is_active',
    ];

    /**
     * Employee this salary component belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
            'amount' => 'decimal:2',
            'is_percentage' => 'boolean',
            'effective_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
