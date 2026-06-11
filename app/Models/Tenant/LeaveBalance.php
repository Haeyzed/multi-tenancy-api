<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Leave balances stored in the tenant database.
 * @property int $id
 * @property string $employee_id
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

    /**
     * Related Employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Related LeaveType.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
