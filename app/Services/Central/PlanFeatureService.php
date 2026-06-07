<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\PlanFeature;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central PlanFeature records and queries.
 */
class PlanFeatureService
{
    /**
     * Get all PlanFeature records.
     *
     * @return Collection<int, PlanFeature>
     */
    public function getAll(): Collection
    {
        return PlanFeature::query()->get();
    }

    /**
     * Get paginated PlanFeature records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, PlanFeature>
     */
    public function getPaginated(int $perPage = 15, ?string $planId = null): LengthAwarePaginator
    {
        return PlanFeature::query()
            ->when($planId, fn ($query) => $query->where('plan_id', $planId))
            ->orderBy('feature_key')
            ->paginate($perPage);
    }

    /**
     * Find PlanFeature by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?PlanFeature
    {
        return PlanFeature::query()->find($id);
    }

    /**
     * Find PlanFeature by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): PlanFeature
    {
        return PlanFeature::query()->findOrFail($id);
    }

    /**
     * Create a new PlanFeature.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PlanFeature
    {
        return PlanFeature::query()->create($data);
    }

    /**
     * Update PlanFeature.
     *
     * @param  PlanFeature  $planFeature  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(PlanFeature $planFeature, array $data): PlanFeature
    {
        $planFeature->update($data);

        return $planFeature->fresh();
    }

    /**
     * Delete PlanFeature.
     *
     * @param  PlanFeature  $planFeature  The model instance to delete.
     */
    public function delete(PlanFeature $planFeature): bool
    {
        return $planFeature->delete();
    }

    /**
     * Filter by plan.
     *
     * @param  string  $planId  Plan UUID to filter by.
     * @return Collection<int, PlanFeature>
     */
    public function getByPlan(string $planId): Collection
    {
        return PlanFeature::query()->where('plan_id', $planId)->get();
    }
}
