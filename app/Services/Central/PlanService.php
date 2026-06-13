<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Plan;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Central subscription plan records and queries.
 *
 * Encapsulates all business logic for plan management, including
 * creation, updates, pagination, filtering, deletion, restoration,
 * and KPI metrics.
 */
class PlanService
{
    /**
     * Get paginated plan records with eager loaded relations.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $isActive Active/inactive filter tokens.
     * @param mixed $isPublic Public/private filter tokens.
     *
     * @return LengthAwarePaginator<int, Plan>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $isActive = null,
        mixed $isPublic = null,
    ): LengthAwarePaginator {
        return Plan::query()
            ->with(['planFeatures'])
            ->search($search)
            ->filterIsActive(QueryFilter::filterList($isActive))
            ->filterIsPublic(QueryFilter::filterList($isPublic))
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * Find plan by ID or fail with eager loaded relations.
     *
     * @param int $id Record identifier.
     *
     * @return Plan
     */
    public function findOrFail(int $id): Plan
    {
        return Plan::query()
            ->with(['planFeatures'])
            ->findOrFail($id);
    }

    /**
     * Create a new plan.
     *
     * @param array<string, mixed> $data
     *
     * @return Plan
     */
    public function create(array $data): Plan
    {
        return Plan::query()->create($data);
    }

    /**
     * Update plan.
     *
     * @param Plan $plan The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return Plan
     */
    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);

        return $plan->fresh(['planFeatures']);
    }

    /**
     * Delete a single plan.
     *
     * @param Plan $plan The model instance to delete.
     *
     * @return bool
     */
    public function delete(Plan $plan): bool
    {
        return $plan->delete();
    }

    /**
     * Delete multiple plans by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Plan::query()->whereIn('id', $ids)->get();
            $deleted = 0;

            foreach ($records as $record) {
                if ($record->delete()) {
                    $deleted++;
                }
            }

            return $deleted;
        });
    }

    /**
     * Restore a soft-deleted plan.
     *
     * @param int $id Trashed record identifier.
     *
     * @return Plan
     */
    public function restore(int $id): Plan
    {
        $model = Plan::withTrashed()->findOrFail($id);
        $model->restore();

        return $model->load(['planFeatures']);
    }

    /**
     * Restore multiple soft-deleted plans by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of restored records.
     */
    public function restoreMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Plan::withTrashed()->whereIn('id', $ids)->get();
            $restored = 0;

            foreach ($records as $record) {
                if ($record->restore()) {
                    $restored++;
                }
            }

            return $restored;
        });
    }

    /**
     * Get active public plans for pricing and signup pages.
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
     *
     * @return list<array{value: int, label: string}>
     */
    public function getOptions(bool $publicOnly = false): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->when($publicOnly, fn ($query) => $query->where('is_public', true))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Plan $plan): array => [
                'value' => $plan->id,
                'label' => $plan->name,
            ])
            ->values()
            ->all();
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
