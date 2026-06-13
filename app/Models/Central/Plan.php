<?php

declare(strict_types=1);

namespace App\Models\Central;

use Database\Factories\Central\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
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
 * @property array<string, mixed>|null $features Marketing/display copy for pricing UI (not used for access control).
 *
 * @method static Builder|Plan search(?string $search)
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory, HasUuids, SoftDeletes;

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

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    /**
     * Scope a query to search by name, slug, or description.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = [];

        foreach ($statuses as $status) {
            $values[] = match ($status) {
                'active' => true,
                'inactive' => false,
                default => null,
            };
        }

        $values = array_values(array_unique(array_filter(
            $values,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($values !== [], fn(Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Filter by public/private visibility tokens (public, private).
     *
     * @param list<string> $values
     */
    public function scopeFilterIsPublic(Builder $query, array $values): void
    {
        $mapped = [];

        foreach ($values as $value) {
            $mapped[] = match ($value) {
                'public' => true,
                'private' => false,
                default => null,
            };
        }

        $mapped = array_values(array_unique(array_filter(
            $mapped,
            static fn (?bool $value): bool => $value !== null,
        )));

        $query->when($mapped !== [], fn(Builder $q) => $q->whereIn('is_public', $mapped));
    }

    /**
     * Tenants currently assigned to this plan.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Enforceable feature limits and flags for this plan (used by middleware and quotas).
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
}
