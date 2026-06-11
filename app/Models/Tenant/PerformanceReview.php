<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Performance reviews stored in the tenant database.
 *
 * @property string $id
 * @property string $employee_id
 * @property string $reviewer_id
 * @property Carbon|null $review_period_start
 * @property Carbon|null $review_period_end
 * @property string $type
 * @property string $status
 * @property string|null $overall_rating
 * @property string|null $summary
 * @property string|null $employee_comments
 * @property string|null $manager_comments
 * @property array<string, mixed>|null $goals
 * @property array<string, mixed>|null $development_plan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PerformanceReview extends TenantModel
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $table = 'performance_reviews';
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'reviewer_id',
        'review_period_start',
        'review_period_end',
        'type',
        'status',
        'overall_rating',
        'summary',
        'employee_comments',
        'manager_comments',
        'goals',
        'development_plan',
    ];

    /**
     * Related Employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
            'review_period_start' => 'date',
            'review_period_end' => 'date',
            'overall_rating' => 'decimal:2',
            'goals' => 'array',
            'development_plan' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
