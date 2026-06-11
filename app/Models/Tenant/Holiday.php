<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Holidays stored in the tenant database.
 * @property int $id
 * @property string|null $name
 * @property Carbon|null $date
 * @property string $type
 * @property bool $is_recurring
 * @property string|null $recurrence_pattern
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Holiday search(?string $search)
 */
class Holiday extends TenantModel
{
    use HasFactory;

    protected $table = 'holidays';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'date',
        'type',
        'is_recurring',
        'recurrence_pattern',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_recurring' => 'boolean',
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
