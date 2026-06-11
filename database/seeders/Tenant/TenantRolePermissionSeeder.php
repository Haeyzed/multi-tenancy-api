<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Enums\Tenant\TenantUserRole;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seed default tenant roles and permissions.
 */
class TenantRolePermissionSeeder extends Seeder
{
    private const GUARD = TenantUserRole::GUARD;

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::query()->where('guard_name', 'web')->update(['guard_name' => self::GUARD]);
        Permission::query()->where('guard_name', 'web')->update(['guard_name' => self::GUARD]);

        foreach (TenantUserRole::permissionNames() as $permissionName) {
            Permission::query()->updateOrCreate(
                ['name' => $permissionName, 'guard_name' => self::GUARD],
            );
        }

        foreach (TenantUserRole::rolePermissions() as $roleName => $permissionNames) {
            $role = Role::query()->updateOrCreate(
                ['name' => $roleName, 'guard_name' => self::GUARD],
            );

            $role->syncPermissions($permissionNames);
        }
    }
}
