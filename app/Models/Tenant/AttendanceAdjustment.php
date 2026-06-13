<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Attendance adjustment stored in the tenant database.
 *
 * @property int $id
 * @property int $attendance_id
 * @property string|null $field_changed
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string|null $reason
 * @property string $adjusted_by
 * @property Carbon|null $adjusted_at
 * @property string|null $approved_by
 * @property Carbon|null $approved_at
 * @property bool $is_approved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AttendanceAdjustment extends TenantModel
{
    use HasFactory;

    protected $table = 'attendance_adjustments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'attendance_id',
        'field_changed',
        'old_value',
        'new_value',
        'reason',
        'adjusted_by',
        'adjusted_at',
        'approved_by',
        'approved_at',
        'is_approved',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'adjusted_at' => 'datetime',
            'approved_at' => 'datetime',
            'is_approved' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
