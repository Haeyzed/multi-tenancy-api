<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Order payments stored in the tenant database.
 *
 * @property int $id
 * @property string $order_id
 * @property string $amount
 * @property string|null $currency
 * @property string $provider
 * @property string|null $provider_payment_id
 * @property string $payment_method
 * @property string|null $card_last4
 * @property string|null $card_brand
 * @property string|null $failure_reason
 * @property string $status
 * @property Carbon|null $paid_at
 * @property string $refunded_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrderPayment extends TenantModel
{
    use HasFactory;

    protected $table = 'order_payments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'amount',
        'currency',
        'provider',
        'provider_payment_id',
        'payment_method',
        'card_last4',
        'card_brand',
        'failure_reason',
        'status',
        'paid_at',
        'refunded_amount',
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
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'refunded_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
