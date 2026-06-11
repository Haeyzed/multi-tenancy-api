<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pick lists stored in the tenant database.
 *
 * @property string $id
 * @property string $warehouse_id
 * @property string $status
 * @property Carbon|null $completed_at
 * @property string|null $picker_id
 * @property string|null $packer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PickList extends TenantModel
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $table = 'pick_lists';
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'warehouse_id',
        'status',
        'completed_at',
        'picker_id',
        'packer_id',
    ];

    /**
     * Related Warehouse.
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
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
