<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Subscription billing cycles stored in the tenant database.
 * @property int $id
 * @property string $subscription_id
 * @property int $cycle_number
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $amount
 * @property string $status
 * @property string|null $invoice_id
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
