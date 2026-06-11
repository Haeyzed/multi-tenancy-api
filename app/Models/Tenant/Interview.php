<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Job interview stored in the tenant database.
 *
 * @property int $id
 * @property string $application_id
 * @property int $stage_id
 * @property Carbon|null $scheduled_at
 * @property int $duration_minutes
 * @property string $location
 * @property string|null $meeting_link
 * @property array<string, mixed>|null $interviewer_ids
 * @property string $status
 * @property string|null $feedback
 * @property string|null $rating
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Interview filterStatus(array $statuses)
 */
class Interview extends TenantModel
{
    use HasFactory;

    protected $table = 'interviews';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'stage_id',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_link',
        'interviewer_ids',
        'status',
        'feedback',
        'rating',
    ];

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
            'scheduled_at' => 'datetime',
            'interviewer_ids' => 'array',
            'rating' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
