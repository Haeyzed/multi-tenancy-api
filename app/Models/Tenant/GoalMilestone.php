<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Milestone within an employee goal stored in the tenant database.
 *
 * @property int $id
 * @property int $goal_id
 * @property string|null $title
 * @property Carbon|null $target_date
 * @property string $status
 * @property Carbon|null $completion_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|GoalMilestone search(?string $search)
 * @method static Builder|GoalMilestone filterStatus(array $statuses)
 */
class GoalMilestone extends TenantModel
{
    use HasFactory;

    protected $table = 'goal_milestones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'goal_id',
        'title',
        'target_date',
        'status',
        'completion_date',
    ];

    /**
     * Goal this milestone belongs to.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Scope a query to search by title.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        });
    }

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
            'target_date' => 'date',
            'completion_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
