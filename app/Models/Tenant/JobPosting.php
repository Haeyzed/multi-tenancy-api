<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Job posting stored in the tenant database.
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $slug
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property string|null $description
 * @property array<string, mixed>|null $requirements
 * @property array<string, mixed>|null $responsibilities
 * @property string $employment_type
 * @property int|null $location_id
 * @property string|null $salary_min
 * @property string|null $salary_max
 * @property string|null $currency
 * @property int $vacancies_count
 * @property string $status
 * @property Carbon|null $published_at
 * @property Carbon|null $closing_date
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|JobPosting search(?string $search)
 * @method static Builder|JobPosting filterStatus(array $statuses)
 */
class JobPosting extends TenantModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'job_postings';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'department_id',
        'designation_id',
        'description',
        'requirements',
        'responsibilities',
        'employment_type',
        'location_id',
        'salary_min',
        'salary_max',
        'currency',
        'vacancies_count',
        'status',
        'published_at',
        'closing_date',
        'created_by',
    ];

    /**
     * Department this job posting belongs to.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Designation for this job posting.
     *
     * @return BelongsTo<Designation, $this>
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Scope a query to search by common searchable columns.
     *
     * @param Builder<JobPosting> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
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
            'requirements' => 'array',
            'responsibilities' => 'array',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'published_at' => 'datetime',
            'closing_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
