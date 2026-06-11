<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Salary advances stored in the tenant database.
 * @property int $id
 * @property string $employee_id
 * @property string $type
 * @property string $amount
 * @property string|null $approved_amount
 * @property string|null $purpose
 * @property int $installments
 * @property string $paid_amount
 * @property string $remaining_amount
 * @property string $status
 * @property string|null $approved_by
 * @property Carbon|null $approved_at
 * @property Carbon|null $deduction_start_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SalaryAdvance extends TenantModel
{
    use HasFactory;

    protected $table = 'salary_advances';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'approved_amount',
        'purpose',
        'installments',
        'paid_amount',
        'remaining_amount',
        'status',
        'approved_by',
        'approved_at',
        'deduction_start_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'deduction_start_date' => 'date',
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
