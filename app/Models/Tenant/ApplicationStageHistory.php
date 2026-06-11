<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Application stage history stored in the tenant database.
 * @property int $id
 * @property string $application_id
 * @property int $stage_id
 * @property string $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $completed_at
 * @property string|null $conducted_by
 * @property string|null $score
 * @property string|null $feedback
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ApplicationStageHistory extends TenantModel
{
    use HasFactory;

    protected $table = 'application_stage_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'stage_id',
        'status',
        'scheduled_at',
        'completed_at',
        'conducted_by',
        'score',
        'feedback',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'score' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
