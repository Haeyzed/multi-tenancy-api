<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Shipments stored in the tenant database.
 *
 * @property int $id
 * @property string $order_id
 * @property string|null $carrier
 * @property string|null $tracking_number
 * @property string|null $tracking_url
 * @property string $status
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $estimated_delivery
 * @property string|null $weight
 * @property string|null $cost
 * @property string|null $label_url
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Shipment extends TenantModel
{
    use HasFactory;

    protected $table = 'shipments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'carrier',
        'tracking_number',
        'tracking_url',
        'status',
        'shipped_at',
        'delivered_at',
        'estimated_delivery',
        'weight',
        'cost',
        'label_url',
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
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'estimated_delivery' => 'date',
            'weight' => 'decimal:2',
            'cost' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
