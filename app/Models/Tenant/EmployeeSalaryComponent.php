<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee salary components stored in the tenant database.
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

    /**
     * Related Employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
