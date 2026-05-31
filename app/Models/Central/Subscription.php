<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\PaymentProvider;
use App\Enums\Central\SubscriptionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Tenant subscription to a billing plan.
 *
 * @property string $id
 * @property string $tenant_id
 * @property string $plan_id
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
 * @property string|null $latest_invoice_id
 */
class Subscription extends Model
{
    use HasFactory, HasUuids;

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
        ];
    }

    /**
     * Tenant that owns this subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Plan billed by this subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Most recent invoice generated for this subscription.
     */
    public function latestInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'latest_invoice_id');
    }

    /**
     * All invoices linked to this subscription.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Billable line items on this subscription.
     */
    public function subscriptionItems(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    /**
     * Usage records recorded against this subscription.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Lifecycle events recorded for this subscription.
     */
    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class);
    }
}
