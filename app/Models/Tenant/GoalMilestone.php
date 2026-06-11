<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Goal milestones stored in the tenant database.
 * @property int $id
 * @property int $goal_id
 * @property string|null $title
 * @property Carbon|null $target_date
 * @property string $status
 * @property Carbon|null $completion_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|GoalMilestone search(?string $search)
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

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'completion_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Goal.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
            );
        });
    }
}
