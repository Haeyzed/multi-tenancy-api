<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Supplier payments stored in the tenant database.
 * @property int $id
 * @property string $supplier_invoice_id
 * @property string $amount
 * @property Carbon|null $payment_date
 * @property string $payment_method
 * @property string|null $transaction_reference
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SupplierPayment extends TenantModel
{
    use HasFactory;

    protected $table = 'supplier_payments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'supplier_invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related SupplierInvoice.
     */
    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }
}
