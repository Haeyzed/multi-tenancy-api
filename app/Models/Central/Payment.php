<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\PaymentMethodType;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\PaymentStatus;
use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tenant payment stored in the central database.
 *
 * @property int $id
 * @property string $tenant_id
 * @property int|null $invoice_id
 * @property int $amount
 * @property string $currency
 * @property PaymentStatus $status
 * @property PaymentProvider $payment_provider
 * @property string|null $provider_payment_id
 * @property PaymentMethodType|null $payment_method_type
 * @property string|null $payment_method_last4
 * @property string|null $payment_method_brand
 * @property string|null $failure_message
 * @property int $refunded_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Payment forTenant(?string $tenantId = null)
 * @method static Builder|Payment search(?string $search)
 * @method static Builder|Payment filterStatus(array $statuses)
 */
class Payment extends Model
{
    use FilterableByTenant;
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'amount',
        'currency',
        'status',
        'payment_provider',
        'provider_payment_id',
        'payment_method_type',
        'payment_method_last4',
        'payment_method_brand',
        'failure_message',
        'refunded_amount',
    ];

    /**
     * Scope a query to search by provider reference, tenant, or invoice number.
     *
     * @param Builder<Payment> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search): void {
            $q->where(function (Builder $q) use ($search): void {
                $q->where('provider_payment_id', 'like', "%{$search}%")
                    ->orWhere('failure_message', 'like', "%{$search}%")
                    ->orWhere('currency', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoice', function (Builder $q) use ($search): void {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by payment status values.
     *
     * @param Builder<Payment> $query
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn (Builder $q): Builder => $q->whereIn('status', $statuses));
    }

    /**
     * Tenant that made this payment.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Invoice settled by this payment.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payment_provider' => PaymentProvider::class,
            'payment_method_type' => PaymentMethodType::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
