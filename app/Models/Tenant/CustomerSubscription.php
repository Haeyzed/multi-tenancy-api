<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Customer subscriptions stored in the tenant database.
 * @property string $id
 * @property string $user_id
 * @property string $plan_id
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
 */
class CustomerSubscription extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'customer_subscriptions';

    public $incrementing = false;

    protected $keyType = 'string';

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

    /**
     * Related User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
