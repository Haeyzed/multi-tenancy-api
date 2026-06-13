<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Employee record stored in the tenant database.
 *
 * @property int $id
 * @property string|null $employee_code
 * @property int|null $user_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $personal_email
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string|null $marital_status
 * @property string|null $nationality
 * @property int|null $national_id
 * @property int|null $avatar_media_id
 * @property string $status
 * @property Carbon|null $hire_date
 * @property Carbon|null $termination_date
 * @property string|null $termination_reason
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property int|null $manager_id
 * @property string $employment_type
 * @property int|null $work_location_id
 * @property int|null $shift_id
 * @property string $base_salary
 * @property string|null $salary_currency
 * @property string $salary_type
 * @property int|null $pay_grade_id
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_name
 * @property int|null $tax_id
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $emergency_contact_relation
 * @property string|null $education_level
 * @property string|null $bio
 * @property string|null $notes
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Employee search(?string $search)
 * @method static Builder|Employee filterStatus(array $statuses)
 */
class Employee extends TenantModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'employees';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'personal_email',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'national_id',
        'avatar_media_id',
        'status',
        'hire_date',
        'termination_date',
        'termination_reason',
        'department_id',
        'designation_id',
        'manager_id',
        'employment_type',
        'work_location_id',
        'shift_id',
        'base_salary',
        'salary_currency',
        'salary_type',
        'pay_grade_id',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'tax_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'education_level',
        'bio',
        'notes',
        'created_by',
    ];

    /**
     * Linked user account for this employee.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Department this employee belongs to.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Job designation for this employee.
     *
     * @return BelongsTo<Designation, $this>
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Primary work location for this employee.
     *
     * @return BelongsTo<WorkLocation, $this>
     */
    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    /**
     * Default shift assigned to this employee.
     *
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Pay grade for this employee's compensation.
     *
     * @return BelongsTo<PayGrade, $this>
     */
    public function payGrade(): BelongsTo
    {
        return $this->belongsTo(PayGrade::class);
    }

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<Employee> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('email', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by status values.
     *
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn(Builder $q) => $q->whereIn('status', $statuses));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'termination_date' => 'date',
            'base_salary' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
