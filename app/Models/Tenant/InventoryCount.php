<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Warehouse inventory count stored in the tenant database.
 *
 * @property int $id
 * @property int $warehouse_id
 * @property string $status
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property string $counted_by
 * @property string|null $approved_by
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|InventoryCount filterStatus(array $statuses)
 */
class InventoryCount extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_counts';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_id',
        'status',
        'started_at',
        'completed_at',
        'counted_by',
        'approved_by',
        'notes',
    ];

    /**
     * Warehouse being counted.
     *
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
