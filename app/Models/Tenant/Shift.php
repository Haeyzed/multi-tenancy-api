<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Shifts stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property string $type
 * @property mixed $start_time
 * @property mixed $end_time
 * @property int $break_duration_minutes
 * @property array<string, mixed>|null $days_of_week
 * @property int $grace_period_minutes
 * @property bool $is_night_shift
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Shift search(?string $search)
 */
class Shift extends TenantModel
{
    use HasFactory;

    protected $table = 'shifts';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'days_of_week',
        'grace_period_minutes',
        'is_night_shift',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            'is_night_shift' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
            );
        });
    }
}
