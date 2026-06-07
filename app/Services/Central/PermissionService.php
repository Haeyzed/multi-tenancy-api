<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central Permission records and queries.
 */
class PermissionService
{
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
     * Get all Permission records.
     *
     * @param  string|null  $search  Optional search term.
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
     * Get paginated Permission records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
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
     * Find Permission by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?Permission
    {
        return $this->query()->find($id);
    }

    /**
     * Find Permission by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): Permission
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * Create a new Permission.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Permission
    {
        return Permission::query()->create($data);
    }

    /**
     * Update Permission.
     *
     * @param  Permission  $permission  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission->fresh();
    }

    /**
     * Delete Permission.
     *
     * @param  Permission  $permission  The model instance to delete.
     */
    public function delete(Permission $permission): bool
    {
        return $permission->delete();
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
