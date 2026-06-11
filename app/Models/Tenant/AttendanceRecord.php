<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Attendance records stored in the tenant database.
 * @property string $id
 * @property string $employee_id
 * @property Carbon|null $date
 * @property int|null $shift_id
 * @property Carbon|null $check_in
 * @property array<string, mixed>|null $check_in_location
 * @property string $check_in_method
 * @property int|null $check_in_photo_media_id
 * @property Carbon|null $check_out
 * @property array<string, mixed>|null $check_out_location
 * @property string|null $check_out_method
 * @property int|null $check_out_photo_media_id
 * @property Carbon|null $break_start
 * @property Carbon|null $break_end
 * @property int $break_duration_minutes
 * @property string $total_working_hours
 * @property string $overtime_hours
 * @property int $late_minutes
 * @property int $early_leave_minutes
 * @property string $status
 * @property string|null $notes
 * @property string|null $approved_by
 * @property Carbon|null $approved_at
 * @property bool $is_manual_entry
 * @property string|null $manual_entry_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AttendanceRecord extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'attendance_records';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'check_in',
        'check_in_location',
        'check_in_method',
        'check_in_photo_media_id',
        'check_out',
        'check_out_location',
        'check_out_method',
        'check_out_photo_media_id',
        'break_start',
        'break_end',
        'break_duration_minutes',
        'total_working_hours',
        'overtime_hours',
        'late_minutes',
        'early_leave_minutes',
        'status',
        'notes',
        'approved_by',
        'approved_at',
        'is_manual_entry',
        'manual_entry_reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_in_location' => 'array',
            'check_out' => 'datetime',
            'check_out_location' => 'array',
            'break_start' => 'datetime',
            'break_end' => 'datetime',
            'total_working_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'approved_at' => 'datetime',
            'is_manual_entry' => 'boolean',
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

    /**
     * Related Shift.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
