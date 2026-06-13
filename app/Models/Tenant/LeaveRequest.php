<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee leave request stored in the tenant database.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $leave_type_id
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $total_days
 * @property string $half_day_type
 * @property string|null $reason
 * @property int|null $attachment_media_id
 * @property string $status
 * @property string|null $approved_by
 * @property Carbon|null $approved_at
 * @property string|null $rejection_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|LeaveRequest filterStatus(array $statuses)
 */
class LeaveRequest extends TenantModel
{
    use HasFactory;

    protected $table = 'leave_requests';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'half_day_type',
        'reason',
        'attachment_media_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    /**
     * Employee who submitted this leave request.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Leave type for this request.
     *
     * @return BelongsTo<LeaveType, $this>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
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
            'start_date' => 'date',
            'end_date' => 'date',
            'total_days' => 'decimal:2',
            'approved_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
