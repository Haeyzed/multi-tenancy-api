<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Inventory counts stored in the tenant database.
 * @property string $id
 * @property string $warehouse_id
 * @property string $status
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property string $counted_by
 * @property string|null $approved_by
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InventoryCount extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'inventory_counts';

    public $incrementing = false;

    protected $keyType = 'string';

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

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related Warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
