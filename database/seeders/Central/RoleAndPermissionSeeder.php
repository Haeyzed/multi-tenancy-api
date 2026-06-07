<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\UserRole;
use App\Models\Central\Permission;
use App\Models\Central\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seed platform roles and permissions for central administrators.
 */
class RoleAndPermissionSeeder extends Seeder
{
    private const GUARD = 'web';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = $this->permissions();

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['name' => $permission['name'], 'guard_name' => self::GUARD],
                ['module' => $permission['module']],
            );
        }

        $allPermissions = Permission::query()
            ->where('guard_name', self::GUARD)
            ->pluck('name')
            ->all();

        $rolePermissions = [
            UserRole::SuperAdmin->value => $allPermissions,
            UserRole::Support->value => [
                'dashboard.view',
                'tenants.view',
                'support.view',
                'support.manage',
                'platform.view',
                'monitoring.view',
            ],
            UserRole::Billing->value => [
                'dashboard.view',
                'tenants.view',
                'billing.view',
                'billing.manage',
                'support.view',
            ],
            UserRole::Technical->value => [
                'dashboard.view',
                'tenants.view',
                'support.view',
                'support.manage',
                'monitoring.view',
                'monitoring.manage',
                'api-keys.view',
                'api-keys.manage',
                'impersonation.use',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::query()->updateOrCreate(
                ['name' => $roleName, 'guard_name' => self::GUARD],
            );

            $role->syncPermissions($permissionNames);
        }
    }

    /**
     * @return list<array{name: string, module: string}>
     */
    private function permissions(): array
    {
        return [
            ['name' => 'dashboard.view', 'module' => 'dashboard'],

            ['name' => 'users.view', 'module' => 'users'],
            ['name' => 'users.create', 'module' => 'users'],
            ['name' => 'users.update', 'module' => 'users'],
            ['name' => 'users.delete', 'module' => 'users'],

            ['name' => 'roles.view', 'module' => 'roles'],
            ['name' => 'roles.create', 'module' => 'roles'],
            ['name' => 'roles.update', 'module' => 'roles'],
            ['name' => 'roles.delete', 'module' => 'roles'],

            ['name' => 'permissions.view', 'module' => 'permissions'],
            ['name' => 'permissions.create', 'module' => 'permissions'],
            ['name' => 'permissions.update', 'module' => 'permissions'],
            ['name' => 'permissions.delete', 'module' => 'permissions'],

            ['name' => 'tenants.view', 'module' => 'tenants'],
            ['name' => 'tenants.create', 'module' => 'tenants'],
            ['name' => 'tenants.update', 'module' => 'tenants'],
            ['name' => 'tenants.delete', 'module' => 'tenants'],

            ['name' => 'billing.view', 'module' => 'billing'],
            ['name' => 'billing.manage', 'module' => 'billing'],

            ['name' => 'support.view', 'module' => 'support'],
            ['name' => 'support.manage', 'module' => 'support'],

            ['name' => 'platform.view', 'module' => 'platform'],
            ['name' => 'platform.manage', 'module' => 'platform'],

            ['name' => 'monitoring.view', 'module' => 'monitoring'],
            ['name' => 'monitoring.manage', 'module' => 'monitoring'],

            ['name' => 'api-keys.view', 'module' => 'api-keys'],
            ['name' => 'api-keys.manage', 'module' => 'api-keys'],

            ['name' => 'impersonation.use', 'module' => 'impersonation'],
        ];
    }
}
