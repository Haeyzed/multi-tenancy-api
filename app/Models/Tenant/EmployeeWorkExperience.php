<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee work experience stored in the tenant database.
 * @property int $id
 * @property string $employee_id
 * @property string|null $company_name
 * @property string|null $job_title
 * @property string|null $description
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property bool $is_current
 * @property string|null $location
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmployeeWorkExperience extends TenantModel
{
    use HasFactory;

    protected $table = 'employee_work_experience';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'company_name',
        'job_title',
        'description',
        'start_date',
        'end_date',
        'is_current',
        'location',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
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
