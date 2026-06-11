<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\PaymentMethodType;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\PaymentStatus;
use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment attempt or settlement against an invoice.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string|null $invoice_id
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
 *
 * @method static Builder|Payment forTenant(?string $tenantId = null)
 * @method static Builder|Payment search(?string $search)
 */
class Payment extends Model
{
    use FilterableByTenant, HasFactory, HasUuids;

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
        ];
    }

    /**
     * Scope a query to search by provider payment ID, failure message, or currency.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('provider_payment_id', 'like', "%{$search}%")
                    ->orWhere('failure_message', 'like', "%{$search}%")
                    ->orWhere('currency', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoice', function (Builder $q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by payment status values.
     *
     * @param  list<string>  $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn (Builder $q) => $q->whereIn('status', $statuses));
    }

    /**
     * Tenant that made this payment.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Invoice settled by this payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
