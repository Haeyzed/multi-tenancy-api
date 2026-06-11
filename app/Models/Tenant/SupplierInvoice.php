<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Supplier invoices stored in the tenant database.
 *
 * @property string $id
 * @property string $supplier_id
 * @property string|null $po_id
 * @property string|null $invoice_number
 * @property Carbon|null $invoice_date
 * @property Carbon|null $due_date
 * @property string $amount
 * @property string $tax_amount
 * @property string $total
 * @property string|null $currency
 * @property string $status
 * @property string $paid_amount
 * @property Carbon|null $paid_at
 * @property string|null $payment_reference
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SupplierInvoice extends TenantModel
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $table = 'supplier_invoices';
    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'supplier_id',
        'po_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'tax_amount',
        'total',
        'currency',
        'status',
        'paid_amount',
        'paid_at',
        'payment_reference',
    ];

    /**
     * Related Supplier.
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
            'invoice_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
