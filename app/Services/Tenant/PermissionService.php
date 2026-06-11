<?php

declare(strict_types=1);

namespace App\Services\Tenant;

use App\Models\Tenant\Permission;
use App\Services\Concerns\DeletesManyRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Tenant store permission records and queries.
 */
class PermissionService
{
    use DeletesManyRecords;

    /**
     * Get all permission records.
     *
     * @return Collection<int, Permission>
     */
    public function getAll(?string $search = null): Collection
    {
        return $this->query()
            ->search($search)
            ->orderBy('name')
            ->get();
    }

    /**
     * Base query for permission records.
     *
     * @return Builder<Permission>
     */
    private function query(): Builder
    {
        return Permission::query();
    }

    /**
     * Get paginated permission records.
     *
     * @return LengthAwarePaginator<int, Permission>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->query()
            ->search($search)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Find permission by ID or fail.
     */
    public function findOrFail(int $id): Permission
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * Create a new permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Permission
    {
        return Permission::query()->create($data);
    }

    /**
     * Update permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission->fresh();
    }

    /**
     * Delete permission.
     */
    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }

    /**
     * Delete multiple permissions by ID.
     *
     * @param  list<int>  $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->deleteManyByIds(Permission::class, $ids);
    }

    /**
     * KPI card metrics for permissions.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $total = Permission::query()->count();
        $withModule = Permission::query()->whereNotNull('module')->count();
        $modules = Permission::query()
            ->whereNotNull('module')
            ->distinct()
            ->count('module');

        return [
            ['key' => 'total', 'label' => 'Total Permissions', 'value' => $total],
            ['key' => 'with_module', 'label' => 'With Module', 'value' => $withModule],
            ['key' => 'modules', 'label' => 'Modules', 'value' => $modules],
        ];
    }
}
