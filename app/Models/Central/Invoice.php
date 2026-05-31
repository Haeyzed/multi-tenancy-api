<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Billing invoice issued to a tenant.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string|null $subscription_id
 * @property string $invoice_number
 * @property InvoiceStatus $status
 * @property int $amount_due
 * @property int $amount_paid
 * @property int $amount_remaining
 * @property string $currency
 * @property Carbon $billing_period_start
 * @property Carbon $billing_period_end
 * @property Carbon $due_date
 * @property Carbon|null $paid_at
 * @property string|null $pdf_url
 * @property string|null $payment_intent_id
 * @property array<string, mixed>|null $line_items
 * @property string|null $notes
 */
class Invoice extends Model
{
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'status',
        'amount_due',
        'amount_paid',
        'amount_remaining',
        'currency',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'paid_at',
        'pdf_url',
        'payment_intent_id',
        'line_items',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'line_items' => 'array',
            'billing_period_start' => 'datetime',
            'billing_period_end' => 'datetime',
            'due_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Tenant billed by this invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Subscription that generated this invoice.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Structured line items attached to this invoice.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payments applied to this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Subscription that references this invoice as its latest invoice.
     */
    public function subscriptionAsLatestInvoice(): HasOne
    {
        return $this->hasOne(Subscription::class, 'latest_invoice_id');
    }
}
