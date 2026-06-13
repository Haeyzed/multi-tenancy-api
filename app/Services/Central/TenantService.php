<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\TenantStatus;
use App\Models\Central\Tenant;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Central tenant records and queries.
 *
 * Encapsulates all business logic for tenant management, including
 * creation, updates, pagination, filtering, deletion, restoration,
 * lifecycle status changes, and KPI metrics.
 */
class TenantService
{
    /**
     * Get paginated tenant records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     * @param mixed $status Lifecycle status filter tokens.
     *
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function getPaginated(
        int $perPage = 15,
        ?string $search = null,
        mixed $status = null,
    ): LengthAwarePaginator {
        $query = Tenant::query()
            ->search($search)
            ->filterStatus(QueryFilter::filterList($status));

        if (request()->filled('tenant_id')) {
            $query->where('id', request('tenant_id'));
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new tenant.
     *
     * @param array<string, mixed> $data
     *
     * @return Tenant
     */
    public function create(array $data): Tenant
    {
        return Tenant::query()->create($data);
    }

    /**
     * Update tenant.
     *
     * @param Tenant $tenant The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return Tenant
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant->fresh();
    }

    /**
     * Delete a single tenant.
     *
     * @param Tenant $tenant The model instance to delete.
     *
     * @return bool
     */
    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }

    /**
     * Delete multiple tenants by ID.
     *
     * @param list<string> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Tenant::query()->whereIn('id', $ids)->get();
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
     * Restore a soft-deleted tenant.
     *
     * @param string $id Trashed record identifier.
     *
     * @return Tenant
     */
    public function restore(string $id): Tenant
    {
        $model = Tenant::withTrashed()->findOrFail($id);
        $model->restore();

        return $model;
    }

    /**
     * Restore multiple soft-deleted tenants by ID.
     *
     * @param list<string> $ids
     *
     * @return int Number of restored records.
     */
    public function restoreMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Tenant::withTrashed()->whereIn('id', $ids)->get();
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
     * Permanently delete a tenant and its data.
     *
     * @param string $id Trashed record identifier.
     *
     * @return bool
     */
    public function forceDelete(string $id): bool
    {
        $model = Tenant::withTrashed()->findOrFail($id);

        return $model->forceDelete();
    }

    /**
     * Find tenant by ID or fail.
     *
     * @param string $id Record identifier.
     *
     * @return Tenant
     */
    public function findOrFail(string $id): Tenant
    {
        return Tenant::query()->findOrFail($id);
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
     * Filter tenants by lifecycle status.
     *
     * @param string $status Status value to filter by.
     *
     * @return Collection<int, Tenant>
     */
    public function getByStatus(string $status): Collection
    {
        return Tenant::query()->where('status', $status)->get();
    }

    /**
     * Update a tenant's lifecycle status.
     *
     * @param Tenant $tenant The tenant to update.
     * @param string $status New status value.
     *
     * @return Tenant
     */
    public function updateStatus(Tenant $tenant, string $status): Tenant
    {
        $tenant->update(['status' => $status]);

        return $tenant->fresh();
    }

    /**
     * Get tenants expiring within the given number of days.
     *
     * @param int $days Number of days ahead to check for expiration.
     *
     * @return Collection<int, Tenant>
     */
    public function getExpiring(int $days = 7): Collection
    {
        return Tenant::query()
            ->whereNotNull('expires_at')
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
