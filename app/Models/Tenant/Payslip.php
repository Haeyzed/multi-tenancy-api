<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Payslips stored in the tenant database.
 *
 * @property int $id
 * @property string $payroll_entry_id
 * @property int|null $media_id
 * @property Carbon|null $generated_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $viewed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Payslip extends TenantModel
{
    use HasFactory;

    protected $table = 'payslips';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'payroll_entry_id',
        'media_id',
        'generated_at',
        'sent_at',
        'viewed_at',
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
            'generated_at' => 'datetime',
            'sent_at' => 'datetime',
            'viewed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
