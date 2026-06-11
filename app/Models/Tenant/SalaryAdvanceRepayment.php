<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Salary advance repayments stored in the tenant database.
 *
 * @property int $id
 * @property int $advance_id
 * @property string|null $payroll_entry_id
 * @property string $amount
 * @property Carbon|null $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SalaryAdvanceRepayment extends TenantModel
{
    use HasFactory;

    protected $table = 'salary_advance_repayments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'advance_id',
        'payroll_entry_id',
        'amount',
        'date',
    ];

    /**
     * Related PayrollEntry.
     */
    public function payrollEntry(): BelongsTo
    {
        return $this->belongsTo(PayrollEntry::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
