<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\FeatureType;
use App\Models\Central\PlanFeature;
use App\Models\Central\Tenant;
use Illuminate\Support\Collection;

/**
 * Resolve plan feature entitlements for tenants.
 */
class PlanEntitlementService
{
    /**
     * Get all resolved features for a tenant keyed by feature_key.
     *
     * @return array<string, bool|int|float|string>
     */
    public function all(Tenant $tenant): array
    {
        $tenant->loadMissing(['plan.planFeatures']);

        if ($tenant->plan === null) {
            return [];
        }

        $features = [];

        foreach ($tenant->plan->planFeatures as $feature) {
            $features[$feature->feature_key] = $this->castFeatureValue($feature);
        }

        foreach ($tenant->plan->features ?? [] as $key => $value) {
            $features[$key] ??= $value;
        }

        return $features;
    }

    /**
     * Determine whether a boolean feature is enabled for the tenant.
     *
     * @param  Tenant  $tenant  Tenant to check.
     * @param  string  $featureKey  Feature identifier (e.g. api_access).
     */
    public function hasFeature(Tenant $tenant, string $featureKey): bool
    {
        $value = $this->getFeature($tenant, $featureKey);

        if ($value === null) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) || $value === 'unlimited';
        }

        return (bool) $value;
    }

    /**
     * Get a single feature value for the tenant.
     *
     * @param  Tenant  $tenant  Tenant to check.
     * @param  string  $featureKey  Feature identifier.
     */
    public function getFeature(Tenant $tenant, string $featureKey): bool|int|float|string|null
    {
        return $this->all($tenant)[$featureKey] ?? null;
    }

    /**
     * Check whether current usage is within a numeric plan limit.
     *
     * @param  Tenant  $tenant  Tenant to check.
     * @param  string  $featureKey  Limit feature key (e.g. max_products).
     * @param  int  $currentUsage  Current usage count.
     */
    public function withinLimit(Tenant $tenant, string $featureKey, int $currentUsage): bool
    {
        $limit = $this->getFeature($tenant, $featureKey);

        if ($limit === null) {
            return false;
        }

        if ($limit === 'unlimited') {
            return true;
        }

        if (! is_numeric($limit)) {
            return false;
        }

        return $currentUsage <= (int) $limit;
    }

    /**
     * Get structured plan features for a tenant.
     *
     * @return Collection<int, PlanFeature>
     */
    public function planFeatures(Tenant $tenant): Collection
    {
        $tenant->loadMissing('plan.planFeatures');

        return $tenant->plan?->planFeatures ?? collect();
    }

    /**
     * Cast a plan feature row to its typed runtime value.
     */
    private function castFeatureValue(PlanFeature $feature): bool|int|float|string
    {
        return match ($feature->feature_type) {
            FeatureType::Boolean => filter_var($feature->feature_value, FILTER_VALIDATE_BOOLEAN),
            FeatureType::Integer => (int) $feature->feature_value,
            FeatureType::Decimal => (float) $feature->feature_value,
            FeatureType::String => $feature->feature_value,
        };
    }
}
