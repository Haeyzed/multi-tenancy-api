<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Job applications stored in the tenant database.
 * @property string $id
 * @property string $job_posting_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property int|null $resume_media_id
 * @property string|null $cover_letter
 * @property string|null $portfolio_url
 * @property string|null $linkedin_url
 * @property string $source
 * @property string|null $referrer_name
 * @property string $status
 * @property string|null $current_salary
 * @property string|null $expected_salary
 * @property string|null $notice_period
 * @property Carbon|null $available_from
 * @property string|null $rating
 * @property string|null $notes
 * @property string|null $assigned_to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|JobApplication search(?string $search)
 */
class JobApplication extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'job_applications';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_posting_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'resume_media_id',
        'cover_letter',
        'portfolio_url',
        'linkedin_url',
        'source',
        'referrer_name',
        'status',
        'current_salary',
        'expected_salary',
        'notice_period',
        'available_from',
        'rating',
        'notes',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'current_salary' => 'decimal:2',
            'expected_salary' => 'decimal:2',
            'available_from' => 'date',
            'rating' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related JobPosting.
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('email', 'like', "%{$search}%")
            );
        });
    }
}
