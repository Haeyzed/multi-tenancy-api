<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Inventory transfer between warehouses stored in the tenant database.
 *
 * @property int $id
 * @property int $from_warehouse_id
 * @property int $to_warehouse_id
 * @property string $status
 * @property Carbon|null $transfer_date
 * @property Carbon|null $received_date
 * @property string|null $notes
 * @property string $created_by
 * @property string|null $received_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|InventoryTransfer filterStatus(array $statuses)
 */
class InventoryTransfer extends TenantModel
{
    use HasFactory;

    protected $table = 'inventory_transfers';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'transfer_date',
        'received_date',
        'notes',
        'created_by',
        'received_by',
    ];

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
            'transfer_date' => 'date',
            'received_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
