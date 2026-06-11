<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Job postings stored in the tenant database.
 * @property string $id
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
 * @method static Builder|JobPosting search(?string $search)
 */
class JobPosting extends TenantModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'job_postings';

    public $incrementing = false;

    protected $keyType = 'string';

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

    /**
     * Related Department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Related Designation.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
            );
        });
    }
}
