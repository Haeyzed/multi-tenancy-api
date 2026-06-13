<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Purchase orders stored in the tenant database.
 *
 * @property int $id
 * @property string|null $po_number
 * @property int $supplier_id
 * @property string $status
 * @property Carbon|null $order_date
 * @property Carbon|null $expected_delivery_date
 * @property Carbon|null $received_date
 * @property string $subtotal
 * @property string $tax_total
 * @property string $shipping_total
 * @property string $grand_total
 * @property string|null $currency
 * @property string|null $notes
 * @property string|null $terms
 * @property string $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class PurchaseOrder extends TenantModel
{
    use HasFactory;

    protected $table = 'purchase_orders';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'po_number',
        'supplier_id',
        'status',
        'order_date',
        'expected_delivery_date',
        'received_date',
        'subtotal',
        'tax_total',
        'shipping_total',
        'grand_total',
        'currency',
        'notes',
        'terms',
        'created_by',
    ];

    /**
     * Related Supplier.
     *
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
            'order_date' => 'date',
            'expected_delivery_date' => 'date',
            'received_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
