<?php

declare(strict_types=1);

namespace App\Models\Central;

use Database\Factories\Central\PlanFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Subscription plan available on the platform.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $tier
 * @property bool $is_active
 * @property bool $is_public
 * @property int $price_monthly
 * @property int $price_yearly
 * @property string $currency
 * @property int $trial_days
 * @property int $sort_order
 * @property array<string, mixed> $features
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'tier',
        'is_active',
        'is_public',
        'price_monthly',
        'price_yearly',
        'currency',
        'trial_days',
        'sort_order',
        'features',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Tenants currently assigned to this plan.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Structured feature limits defined for this plan.
     */
    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * Active and historical subscriptions on this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Line items billed under this plan.
     */
    public function subscriptionItems(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    /**
     * Invoice line items referencing this plan.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Subscription lifecycle events where this plan was the previous plan.
     */
    public function subscriptionEventsFromPlan(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class, 'from_plan_id');
    }

    /**
     * Subscription lifecycle events where this plan was the new plan.
     */
    public function subscriptionEventsToPlan(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class, 'to_plan_id');
    }
}
