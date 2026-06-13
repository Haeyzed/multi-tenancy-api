<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Customer subscription to a plan stored in the tenant database.
 *
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property string $status
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $next_billing_date
 * @property Carbon|null $last_billing_date
 * @property string|null $cancellation_reason
 * @property string|null $cancelled_by
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|CustomerSubscription filterStatus(array $statuses)
 */
class CustomerSubscription extends TenantModel
{
    use HasFactory;

    protected $table = 'customer_subscriptions';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'start_date',
        'end_date',
        'next_billing_date',
        'last_billing_date',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    /**
     * Customer who owns this subscription.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
            'start_date' => 'date',
            'end_date' => 'date',
            'next_billing_date' => 'date',
            'last_billing_date' => 'date',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
