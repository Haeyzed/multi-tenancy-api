<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\SubscriptionStatus;
use App\Models\Concerns\FilterableByTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Tenant subscription stored in the central database.
 *
 * @property int $id
 * @property string $tenant_id
 * @property int $plan_id
 * @property SubscriptionStatus $status
 * @property BillingCycle $billing_cycle
 * @property Carbon $current_period_start
 * @property Carbon $current_period_end
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property PaymentProvider $payment_provider
 * @property string|null $payment_provider_id
 * @property string|null $payment_method_id
 * @property int|null $latest_invoice_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Subscription forTenant(?string $tenantId = null)
 * @method static Builder|Subscription search(?string $search)
 * @method static Builder|Subscription filterStatus(array $statuses)
 */
class Subscription extends Model
{
    use FilterableByTenant;
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'payment_provider',
        'payment_provider_id',
        'payment_method_id',
        'latest_invoice_id',
    ];

    /**
     * Scope a query to search by tenant, plan, status, or provider reference.
     *
     * @param Builder<Subscription> $query
     * @param string|null $search
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search): void {
            $q->where(function (Builder $q) use ($search): void {
                $q->where('cancellation_reason', 'like', "%{$search}%")
                    ->orWhere('payment_provider_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%")
                            ->orWhere('domain', 'like', "%{$search}%");
                    })
                    ->orWhereHas('plan', function (Builder $q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        });
    }

    /**
     * Filter by subscription status values.
     *
     * @param Builder<Subscription> $query
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn (Builder $q): Builder => $q->whereIn('status', $statuses));
    }

    /**
     * Tenant that owns this subscription.
     *
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Plan billed by this subscription.
     *
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Most recent invoice generated for this subscription.
     *
     * @return BelongsTo<Invoice, $this>
     */
    public function latestInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'latest_invoice_id');
    }

    /**
     * All invoices linked to this subscription.
     *
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Billable line items on this subscription.
     *
     * @return HasMany<SubscriptionItem, $this>
     */
    public function subscriptionItems(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    /**
     * Usage records recorded against this subscription.
     *
     * @return HasMany<UsageRecord, $this>
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Lifecycle events recorded for this subscription.
     *
     * @return HasMany<SubscriptionEvent, $this>
     */
    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'billing_cycle' => BillingCycle::class,
            'payment_provider' => PaymentProvider::class,
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
