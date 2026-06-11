<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\BillingCycle;
use App\Enums\Central\SubscriptionStatus;
use App\Enums\Central\TenantStatus;
use Database\Factories\Central\TenantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * Tenant record managed in the central database.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $database
 * @property string $domain
 * @property TenantStatus $status
 * @property string|null $plan_id
 * @property BillingCycle $billing_cycle
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $subscribed_at
 * @property Carbon|null $expires_at
 * @property string $owner_email
 * @property string $owner_name
 * @property array<string, mixed>|null $settings
 * @property array<string, mixed>|null $meta
 * @property array<string, mixed>|null $data
 *
 * @method static Builder|Tenant search(?string $search)
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    /** @use HasFactory<TenantFactory> */
    use HasDatabase, HasDomains, HasFactory, HasUuids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'database',
        'domain',
        'status',
        'plan_id',
        'billing_cycle',
        'trial_ends_at',
        'subscribed_at',
        'expires_at',
        'owner_email',
        'owner_name',
        'settings',
        'meta',
        'data',
    ];

    /**
     * Real database columns (non-virtual) for Stancl's data column handling.
     *
     * @return list<string>
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'database',
            'domain',
            'status',
            'plan_id',
            'billing_cycle',
            'trial_ends_at',
            'subscribed_at',
            'expires_at',
            'owner_email',
            'owner_name',
            'settings',
            'meta',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }

    /**
     * Scope a query to search by core tenant fields.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhere('database', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('owner_email', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by tenant lifecycle status values.
     *
     * @param list<string> $statuses
     */
    public function scopeFilterStatus(Builder $query, array $statuses): void
    {
        $query->when($statuses !== [], fn(Builder $q) => $q->whereIn('status', $statuses));
    }

    /**
     * Subscription plan assigned to this tenant.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Domains registered for this tenant.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /**
     * Key-value configuration entries for this tenant.
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(TenantConfig::class);
    }

    /**
     * Impersonation tokens generated for this tenant.
     */
    public function impersonationTokens(): HasMany
    {
        return $this->hasMany(TenantImpersonationToken::class);
    }

    /**
     * Subscriptions billed for this tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Current active or trialing subscription for this tenant.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', [
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Trialing->value,
            ])
            ->latest('created_at');
    }

    /**
     * Invoices issued to this tenant.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Payments collected from this tenant.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Stored payment methods for this tenant.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Usage records tracked for this tenant.
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Support tickets opened by this tenant.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(TenantSupportTicket::class);
    }

    /**
     * API keys issued to this tenant.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Health check results recorded for this tenant.
     */
    public function healthChecks(): HasMany
    {
        return $this->hasMany(TenantHealthCheck::class);
    }

    /**
     * Daily aggregated metrics for this tenant.
     */
    public function dailyMetrics(): HasMany
    {
        return $this->hasMany(TenantMetric::class);
    }

    /**
     * Error logs associated with this tenant.
     */
    public function errorLogs(): HasMany
    {
        return $this->hasMany(ErrorLog::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TenantStatus::class,
            'billing_cycle' => BillingCycle::class,
            'settings' => 'array',
            'meta' => 'array',
            'data' => 'array',
            'trial_ends_at' => 'datetime',
            'subscribed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
