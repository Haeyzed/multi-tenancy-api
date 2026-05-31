<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Plan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Plan records and queries.
 */
class PlanService
{
    /**
     * Get all Plan records.
     *
     * @return Collection<int, Plan>
     */
    public function getAll(): Collection
    {
        return Plan::query()->get();
    }

    /**
     * Get paginated Plan records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, Plan>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Plan::query()->paginate($perPage);
    }

    /**
     * Find Plan by ID.
     *
     * @param  string  $id  Record identifier.
     */
    public function find(string $id): ?Plan
    {
        return Plan::query()->find($id);
    }

    /**
     * Find Plan by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(string $id): Plan
    {
        return Plan::query()->findOrFail($id);
    }

    /**
     * Create a new Plan.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Plan
    {
        return Plan::query()->create($data);
    }

    /**
     * Update Plan.
     *
     * @param  Plan  $plan  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->query()->update($data);

        return $plan->fresh();
    }

    /**
     * Delete Plan.
     *
     * @param  Plan  $plan  The model instance to delete.
     */
    public function delete(Plan $plan): bool
    {
        return $plan->query()->delete() > 0;
    }

    /**
     * Restore soft-deleted Plan.
     *
     * @param  string  $id  Trashed record identifier.
     */
    public function restore(string $id): Plan
    {
        $model = Plan::withTrashed()->findOrFail($id);
        $model->query()->restore();

        return $model;
    }

    /**
     * Force delete Plan.
     *
     * @param  string  $id  Trashed record identifier.
     */
    public function forceDelete(string $id): bool
    {
        $model = Plan::withTrashed()->findOrFail($id);

        return $model->query()->forceDelete() > 0;
    }

    /**
     * Get only active records.
     *
     * @return Collection<int, Plan>
     */
    public function getActive(): Collection
    {
        return Plan::query()->where('is_active', true)->get();
    }

    /**
     * Get public active plans ordered by tier.
     *
     * @return Collection<int, Plan>
     */
    public function getPublicPlans(): Collection
    {
        return Plan::query()->where('is_active', true)
            ->where('is_public', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Load plan features onto the given plan.
     *
     * @param  Plan  $plan  The plan to eager load features for.
     */
    public function getWithFeatures(Plan $plan): Plan
    {
        return $plan->query()->with('planFeatures')->first();
    }
}
