<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Hiring pipeline stage for a job posting stored in the tenant database.
 *
 * @property int $id
 * @property int $job_posting_id
 * @property string|null $name
 * @property int $order
 * @property bool $is_required
 * @property string $stage_type
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|ApplicationStage search(?string $search)
 * @method static Builder|ApplicationStage filterIsActive(array $statuses)
 */
class ApplicationStage extends TenantModel
{
    use HasFactory;

    protected $table = 'application_stages';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_posting_id',
        'name',
        'order',
        'is_required',
        'stage_type',
        'is_active',
    ];

    /**
     * Job posting this stage belongs to.
     *
     * @return BelongsTo<JobPosting, $this>
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<ApplicationStage> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param Builder<ApplicationStage> $query
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = [];

        foreach ($statuses as $status) {
            $values[] = match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            };
        }

        $values = array_values(array_unique(array_filter(
            $values,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($values !== [], fn (Builder $q): Builder => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
