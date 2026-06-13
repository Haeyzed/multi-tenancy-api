<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Onboarding tasks stored in the tenant database.
 *
 * @property int $id
 * @property int $checklist_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $assigned_to
 * @property Carbon|null $due_date
 * @property Carbon|null $completed_at
 * @property bool $is_required
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|OnboardingTask search(?string $search)
 */
class OnboardingTask extends TenantModel
{
    use HasFactory;

    protected $table = 'onboarding_tasks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'checklist_id',
        'title',
        'description',
        'assigned_to',
        'due_date',
        'completed_at',
        'is_required',
        'sort_order',
    ];

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<OnboardingTask> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'is_required' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
