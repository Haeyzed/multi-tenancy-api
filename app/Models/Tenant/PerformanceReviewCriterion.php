<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Performance review criteria stored in the tenant database.
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property string $category
 * @property string $max_score
 * @property string $weight
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|PerformanceReviewCriterion search(?string $search)
 * @method static Builder|PerformanceReviewCriterion filterIsActive(array $statuses)
 */
class PerformanceReviewCriterion extends TenantModel
{
    use HasFactory;

    protected $table = 'performance_review_criteria';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'category',
        'max_score',
        'weight',
        'is_active',
        'sort_order',
    ];

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = QueryFilter::booleanStatuses($statuses);

        $query->when($values !== [], fn(Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */


    protected function casts(): array
    {
        return [
            'max_score' => 'decimal:2',
            'weight' => 'decimal:2',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
