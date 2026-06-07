<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\TenantStatus;
use App\Models\Central\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Tenant records and queries.
 */
class TenantService
{
    /**
     * Get all Tenant records.
     *
     * @param  string|null  $search  Optional search term.
     * @return Collection<int, Tenant>
     */
    public function getAll(?string $search = null): Collection
    {
        return Tenant::query()
            ->search($search)
            ->get();
    }

    /**
     * Get paginated Tenant records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Tenant::query()->search($search);

        if (request()->filled('tenant_id')) {
            $query->where('id', request('tenant_id'));
        }

        return $query->paginate($perPage);
    }

    /**
     * Find Tenant by ID.
     *
     * @param  string  $id  Record identifier.
     */
    public function find(string $id): ?Tenant
    {
        return Tenant::query()->find($id);
    }

    /**
     * Find Tenant by ID or fail.
     *
     * @param  string  $id  Record identifier.
     */
    public function findOrFail(string $id): Tenant
    {
        return Tenant::query()->findOrFail($id);
    }

    /**
     * Create a new Tenant.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tenant
    {
        return Tenant::query()->create($data);
    }

    /**
     * Update Tenant.
     *
     * @param  Tenant  $tenant  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->fresh();
    }

    /**
     * Delete Tenant.
     *
     * @param  Tenant  $tenant  The model instance to delete.
     */
    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }

    /**
     * Restore soft-deleted Tenant.
     *
     * @param  string  $id  Trashed record identifier.
     */
    public function restore(string $id): Tenant
    {
        $model = Tenant::withTrashed()->findOrFail($id);
        $model->restore();

        return $model;
    }

    /**
     * Force delete Tenant.
     *
     * @param  string  $id  Trashed record identifier.
     */
    public function forceDelete(string $id): bool
    {
        $model = Tenant::withTrashed()->findOrFail($id);

        return $model->forceDelete();
    }

    /**
     * Get active tenants as value/label pairs for select inputs.
     *
     * @return list<array{value: string, label: string}>
     */
    public function getOptions(): array
    {
        return Tenant::query()
            ->where('status', TenantStatus::Active)
            ->orderBy('name')
            ->get()
            ->map(fn (Tenant $tenant): array => [
                'value' => $tenant->id,
                'label' => $tenant->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Filter by status.
     *
     * @param  string  $status  Status value to filter by.
     * @return Collection<int, Tenant>
     */
    public function getByStatus(string $status): Collection
    {
        return Tenant::query()->where('status', $status)->get();
    }

    /**
     * Filter by plan.
     *
     * @param  string  $planId  Plan UUID to filter by.
     * @return Collection<int, Tenant>
     */
    public function getByPlan(string $planId): Collection
    {
        return Tenant::query()->where('plan_id', $planId)->get();
    }

    /**
     * Get tenants by status with plan loaded.
     *
     * @param  string  $status  Tenant status to filter by.
     * @return Collection<int, Tenant>
     */
    public function getWithPlan(string $status): Collection
    {
        return Tenant::query()->with('plan')->where('status', $status)->get();
    }

    /**
     * Update a tenant's lifecycle status.
     *
     * @param  Tenant  $tenant  The tenant to update.
     * @param  string  $status  New status value.
     */
    public function updateStatus(Tenant $tenant, string $status): Tenant
    {
        $tenant->update(['status' => $status]);

        return $tenant->fresh();
    }

    /**
     * Get tenants expiring within the given number of days.
     *
     * @param  int  $days  Number of days ahead to check for expiration.
     * @return Collection<int, Tenant>
     */
    public function getExpiring(int $days = 7): Collection
    {
        return Tenant::query()->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>=', now())
            ->get();
    }

    /**
     * KPI card metrics for tenants.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $query = Tenant::query();

        if (request()->filled('tenant_id')) {
            $query->where('id', request('tenant_id'));
        }

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $expiringSoon = (clone $query)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(7))
            ->where('expires_at', '>=', now())
            ->count();

        $onTrial = (clone $query)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>=', now())
            ->count();

        return [
            ['key' => 'total', 'label' => 'Total Tenants', 'value' => (int) $counts->sum()],
            ['key' => 'active', 'label' => 'Active', 'value' => (int) ($counts[TenantStatus::Active->value] ?? 0)],
            ['key' => 'pending', 'label' => 'Pending', 'value' => (int) ($counts[TenantStatus::Pending->value] ?? 0)],
            ['key' => 'suspended', 'label' => 'Suspended', 'value' => (int) ($counts[TenantStatus::Suspended->value] ?? 0)],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'value' => (int) ($counts[TenantStatus::Cancelled->value] ?? 0)],
            ['key' => 'on_trial', 'label' => 'On Trial', 'value' => $onTrial],
            ['key' => 'expiring_soon', 'label' => 'Expiring Soon', 'value' => $expiringSoon],
        ];
    }
}
