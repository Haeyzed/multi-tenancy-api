<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Pick list items stored in the tenant database.
 *
 * @property int $id
 * @property int $pick_list_id
 * @property int $order_item_id
 * @property int $quantity
 * @property int $picked_qty
 * @property int|null $bin_id
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PickListItem extends TenantModel
{
    use HasFactory;

    protected $table = 'pick_list_items';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pick_list_id',
        'order_item_id',
        'quantity',
        'picked_qty',
        'bin_id',
        'status',
        'notes',
    ];

    /**
     * Related PickList.
     *
     * @return BelongsTo<PickList, $this>
     */
    public function pickList(): BelongsTo
    {
        return $this->belongsTo(PickList::class);
    }

    /**
     * Related OrderItem.
     *
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
