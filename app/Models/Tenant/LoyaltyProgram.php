<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Loyalty programs stored in the tenant database.
 * @property string $id
 * @property string|null $name
 * @property string|null $description
 * @property string $points_per_currency
 * @property int $min_points_redemption
 * @property string $point_value
 * @property bool $is_active
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|LoyaltyProgram search(?string $search)
 */
class LoyaltyProgram extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'loyalty_programs';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'points_per_currency',
        'min_points_redemption',
        'point_value',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'points_per_currency' => 'decimal:2',
            'point_value' => 'decimal:2',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
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
