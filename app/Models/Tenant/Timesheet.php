<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Timesheets stored in the tenant database.
 *
 * @property int $id
 * @property int $employee_id
 * @property int|null $project_id
 * @property int|null $task_id
 * @property string|null $description
 * @property Carbon|null $start_time
 * @property Carbon|null $end_time
 * @property int $duration_minutes
 * @property bool $is_billable
 * @property string|null $billable_rate
 * @property Carbon|null $date
 * @property string|null $approved_by
 * @property Carbon|null $approved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Timesheet extends TenantModel
{
    use HasFactory;

    protected $table = 'timesheets';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'project_id',
        'task_id',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_billable',
        'billable_rate',
        'date',
        'approved_by',
        'approved_at',
    ];

    /**
     * Related Employee.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_billable' => 'boolean',
            'billable_rate' => 'decimal:2',
            'date' => 'date',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
