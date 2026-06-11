<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Inventory transfers stored in the tenant database.
 * @property string $id
 * @property string $from_warehouse_id
 * @property string $to_warehouse_id
 * @property string $status
 * @property Carbon|null $transfer_date
 * @property Carbon|null $received_date
 * @property string|null $notes
 * @property string $created_by
 * @property string|null $received_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InventoryTransfer extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'inventory_transfers';

    public $incrementing = false;

    protected $keyType = 'string';

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
