<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Subscription billing cycles stored in the tenant database.
 *
 * @property int $id
 * @property int $subscription_id
 * @property int $cycle_number
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $amount
 * @property string $status
 * @property int|null $invoice_id
 * @property Carbon|null $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SubscriptionBillingCycle extends TenantModel
{
    use HasFactory;

    protected $table = 'subscription_billing_cycles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'cycle_number',
        'start_date',
        'end_date',
        'amount',
        'status',
        'invoice_id',
        'paid_at',
    ];

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
            'start_date' => 'date',
            'end_date' => 'date',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
