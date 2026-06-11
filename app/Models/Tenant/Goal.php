<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Goals stored in the tenant database.
 * @property int $id
 * @property string $employee_id
 * @property string|null $title
 * @property string|null $description
 * @property string $type
 * @property string $status
 * @property Carbon|null $start_date
 * @property Carbon|null $target_date
 * @property int $completion_percentage
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Goal search(?string $search)
 */
class Goal extends TenantModel
{
    use HasFactory;

    protected $table = 'goals';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'title',
        'description',
        'type',
        'status',
        'start_date',
        'target_date',
        'completion_percentage',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
