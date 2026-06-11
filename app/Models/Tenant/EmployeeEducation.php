<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee education record stored in the tenant database.
 *
 * @property int $id
 * @property string $employee_id
 * @property string|null $institution
 * @property string|null $degree
 * @property string|null $field_of_study
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property bool $is_completed
 * @property string|null $grade
 * @property int|null $media_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmployeeEducation extends TenantModel
{
    use HasFactory;

    protected $table = 'employee_education';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'institution',
        'degree',
        'field_of_study',
        'start_date',
        'end_date',
        'is_completed',
        'grade',
        'media_id',
    ];

    /**
     * Employee this education record belongs to.
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
            'start_date' => 'date',
            'end_date' => 'date',
            'is_completed' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
