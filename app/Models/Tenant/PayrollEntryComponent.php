<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Payroll entry components stored in the tenant database.
 * @property int $id
 * @property string $payroll_entry_id
 * @property int $component_id
 * @property string $amount
 * @property string $calculated_amount
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PayrollEntryComponent extends TenantModel
{
    use HasFactory;

    protected $table = 'payroll_entry_components';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'payroll_entry_id',
        'component_id',
        'amount',
        'calculated_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'calculated_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related PayrollEntry.
     */
    public function payrollEntry(): BelongsTo
    {
        return $this->belongsTo(PayrollEntry::class);
    }
}
