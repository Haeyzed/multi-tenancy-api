<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\InvoiceStatus;
use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Tenant billing invoice stored in the central database.
 *
 * @property int $id
 * @property string $tenant_id
 * @property int|null $subscription_id
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Invoice forTenant(?string $tenantId = null)
 * @method static Builder|Invoice search(?string $search)
 * @method static Builder|Invoice filterStatus(array $statuses)
 */
class Invoice extends Model
{
    use FilterableByTenant;
    use HasFactory;

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
     * Scope a query to search by invoice number, notes, tenant, or payment intent ID.
     *
     * @param Builder<Invoice> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search): void {
            $q->where(function (Builder $q) use ($search): void {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('payment_intent_id', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by invoice status values.
     *
     * @param Builder<Invoice> $query
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn (Builder $q): Builder => $q->whereIn('status', $statuses));
    }

    /**
     * Tenant billed by this invoice.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Subscription that generated this invoice.
     *
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Structured line items attached to this invoice.
     *
     * @return HasMany<InvoiceItem, $this>
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Payments applied to this invoice.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Subscription that references this invoice as its latest invoice.
     *
     * @return HasOne<Subscription, $this>
     */
    public function subscriptionAsLatestInvoice(): HasOne
    {
        return $this->hasOne(Subscription::class, 'latest_invoice_id');
    }

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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
