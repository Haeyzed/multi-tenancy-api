<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Employee leave balance stored in the tenant database.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $leave_type_id
 * @property int $year
 * @property string $entitled_days
 * @property string $used_days
 * @property string $pending_days
 * @property string $carried_forward_days
 * @property string $forfeited_days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LeaveBalance extends TenantModel
{
    use HasFactory;

    protected $table = 'leave_balances';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'entitled_days',
        'used_days',
        'pending_days',
        'carried_forward_days',
        'forfeited_days',
    ];

    /**
     * Employee this balance belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Leave type for this balance.
     *
     * @return BelongsTo<LeaveType, $this>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'entitled_days' => 'decimal:2',
            'used_days' => 'decimal:2',
            'pending_days' => 'decimal:2',
            'carried_forward_days' => 'decimal:2',
            'forfeited_days' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
