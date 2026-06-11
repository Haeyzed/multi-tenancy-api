<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Plan;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Plan records and queries.
 */
class PlanService
{
    use DeletesManyRecords;

    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'planFeatures',
    ];

    /**
     * Get all Plan records.
     *
     * @param string|null $search Optional search term.
     * @return Collection<int, Plan>
     */
    public function getAll(?string $search = null): Collection
    {
        return $this->queryWithDetails()
            ->search($search)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Base query with plan detail relations.
     *
     * @return Builder<Plan>
     */
    private function queryWithDetails(): Builder
    {
        return Plan::query()->with(self::DETAIL_RELATIONS);
    }

    /**
     * Get paginated Plan records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @return LengthAwarePaginator<int, Plan>
     */

    /**
     * @param list<string> $isActive
     * @param list<string> $isPublic
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null,
        array   $isActive = [],
        array   $isPublic = [],
    ): LengthAwarePaginator
    {
        return $this->queryWithDetails()
            ->search($search)
            ->filterIsActive($isActive)
            ->filterIsPublic($isPublic)
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * Find Plan by ID.
     *
     * @param string $id Record identifier.
     */
    public function find(string $id): ?Plan
    {
        return $this->queryWithDetails()->find($id);
    }

    /**
     * Create a new Plan.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Plan
    {
        return Plan::query()->create($data);
    }

    /**
     * Update Plan.
     *
     * @param Plan $plan The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);

        return $plan->fresh();
    }

    /**
     * Delete Plan.
     *
     * @param Plan $plan The model instance to delete.
     */
    public function delete(Plan $plan): bool
    {
        return $plan->delete();
    }

    /**
     * Delete multiple plans by ID.
     *
     * @param list<string> $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(Plan::class, $ids);
    }

    /**
     * Restore soft-deleted Plan.
     *
     * @param string $id Trashed record identifier.
     */
    public function restore(string $id): Plan
    {
        $model = Plan::withTrashed()->findOrFail($id);
        $model->restore();

        return $model;
    }

    /**
     * Find Plan by ID or fail.
     *
     * @param string $id Record identifier.
     */
    public function findOrFail(string $id): Plan
    {
        return $this->queryWithDetails()->findOrFail($id);
    }

    /**
     * Force delete Plan.
     *
     * @param string $id Trashed record identifier.
     */
    public function forceDelete(string $id): bool
    {
        $model = Plan::withTrashed()->findOrFail($id);

        return $model->forceDelete();
    }

    /**
     * Get only active records.
     *
     * @return Collection<int, Plan>
     */
    public function getActive(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get active public plans for pricing/signup pages (includes display + enforceable features).
     *
     * @return Collection<int, Plan>
     */
    public function getPublicPlans(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->where('is_public', true)
            ->with('planFeatures')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get active plans as value/label pairs for select inputs.
     *
     * @param bool $publicOnly When true, only plans marked public (self-service signup).
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(bool $publicOnly = false): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->when($publicOnly, fn($query) => $query->where('is_public', true))
            ->orderBy('sort_order')
            ->get()
            ->map(fn(Plan $plan): array => [
                'value' => $plan->id,
                'label' => $plan->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Load plan features onto the given plan.
     *
     * @param Plan $plan The plan to eager load features for.
     */
    public function getWithFeatures(Plan $plan): Plan
    {
        return $plan->load('planFeatures');
    }

    /**
     * KPI card metrics for plans.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Plan::query()->count();
        $active = Plan::query()->where('is_active', true)->count();
        $public = Plan::query()->where('is_public', true)->count();
        $withFeatures = Plan::query()->whereHas('planFeatures')->count();

        return [
            ['key' => 'total', 'label' => 'Total Plans', 'value' => $total],
            ['key' => 'active', 'label' => 'Active', 'value' => $active],
            ['key' => 'public', 'label' => 'Public', 'value' => $public],
            ['key' => 'with_features', 'label' => 'With Features', 'value' => $withFeatures],
        ];
    }
}
