<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Order refunds stored in the tenant database.
 *
 * @property int $id
 * @property string $order_id
 * @property int|null $order_item_id
 * @property string $amount
 * @property string|null $reason
 * @property string $status
 * @property string|null $processed_by
 * @property Carbon|null $processed_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderRefund extends TenantModel
{
    use HasFactory;

    protected $table = 'order_refunds';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'order_item_id',
        'amount',
        'reason',
        'status',
        'processed_by',
        'processed_at',
        'notes',
    ];

    /**
     * Related Order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Related OrderItem.
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
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
