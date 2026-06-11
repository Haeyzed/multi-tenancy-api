<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Offboarding records stored in the tenant database.
 *
 * @property int $id
 * @property string $employee_id
 * @property Carbon|null $resignation_date
 * @property Carbon|null $last_working_date
 * @property string $reason
 * @property string|null $exit_interview_notes
 * @property string|null $handover_notes
 * @property string $clearance_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OffboardingRecord extends TenantModel
{
    use HasFactory;

    protected $table = 'offboarding_records';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'resignation_date',
        'last_working_date',
        'reason',
        'exit_interview_notes',
        'handover_notes',
        'clearance_status',
    ];

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
            'resignation_date' => 'date',
            'last_working_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
