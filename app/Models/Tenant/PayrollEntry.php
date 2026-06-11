<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Payroll entries stored in the tenant database.
 *
 * @property string $id
 * @property string $payroll_period_id
 * @property string $employee_id
 * @property string $base_salary
 * @property int $working_days
 * @property int $present_days
 * @property int $absent_days
 * @property int $leave_days
 * @property string $overtime_hours
 * @property string $overtime_amount
 * @property string $bonus
 * @property string $commission
 * @property string $allowances_total
 * @property string $deductions_total
 * @property string $tax_amount
 * @property string $net_salary
 * @property string $gross_salary
 * @property string $payment_status
 * @property Carbon|null $paid_at
 * @property string $payment_method
 * @property string|null $transaction_reference
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PayrollEntry extends TenantModel
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $table = 'payroll_entries';
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'base_salary',
        'working_days',
        'present_days',
        'absent_days',
        'leave_days',
        'overtime_hours',
        'overtime_amount',
        'bonus',
        'commission',
        'allowances_total',
        'deductions_total',
        'tax_amount',
        'net_salary',
        'gross_salary',
        'payment_status',
        'paid_at',
        'payment_method',
        'transaction_reference',
    ];

    /**
     * Related PayrollPeriod.
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /**
     * Related Employee.
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
            'base_salary' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'bonus' => 'decimal:2',
            'commission' => 'decimal:2',
            'allowances_total' => 'decimal:2',
            'deductions_total' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
