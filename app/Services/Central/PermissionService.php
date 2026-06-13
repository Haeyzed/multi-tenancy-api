<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Central Spatie permission records and queries.
 *
 * Encapsulates all business logic for permission management, including
 * creation, updates, pagination, deletion, and KPI metrics.
 */
class PermissionService
{
    /**
     * Get paginated permission records.
     *
     * @param int $perPage Number of records per page.
     * @param string|null $search Optional search term.
     *
     * @return LengthAwarePaginator<int, Permission>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return Permission::query()
            ->search($search)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Find permission by ID or fail.
     *
     * @param int $id Record identifier.
     *
     * @return Permission
     */
    public function findOrFail(int $id): Permission
    {
        return Permission::query()->findOrFail($id);
    }

    /**
     * Create a new permission.
     *
     * @param array<string, mixed> $data
     *
     * @return Permission
     */
    public function create(array $data): Permission
    {
        return Permission::query()->create($data);
    }

    /**
     * Update permission.
     *
     * @param Permission $permission The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     *
     * @return Permission
     */
    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission->fresh();
    }

    /**
     * Delete a single permission.
     *
     * @param Permission $permission The model instance to delete.
     *
     * @return bool
     */
    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }

    /**
     * Delete multiple permissions by ID.
     *
     * @param list<int> $ids
     *
     * @return int Number of deleted records.
     */
    public function deleteMany(array $ids): int
    {
        return DB::transaction(function () use ($ids): int {
            $records = Permission::query()->whereIn('id', $ids)->get();
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
