<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenantId = $argv[1] ?? null;

if ($tenantId === null) {
    fwrite(STDERR, "Usage: php scripts/fix-tenant-permissions.php <tenant-id>\n");
    exit(1);
}

$tenant = App\Models\Central\Tenant::query()->findOrFail($tenantId);

$tenant->run(function (): void {
    $migration = require database_path('migrations/tenant/2026_06_13_100011_use_uuid_model_morph_key_in_permission_tables.php');
    $migration->up();

    $tenant = tenant();
    $user = App\Models\Tenant\User::query()
        ->where('email', $tenant->owner_email)
        ->first();

    if ($user === null) {
        echo "Owner user not found\n";
        return;
    }

    $ownerRole = App\Models\Tenant\Role::query()
        ->where('name', App\Enums\Tenant\TenantUserRole::StoreOwner->value)
        ->where('guard_name', App\Enums\Tenant\TenantUserRole::GUARD)
        ->first();

    if ($ownerRole !== null && ! $user->hasRole($ownerRole)) {
        $user->assignRole($ownerRole);
        echo "Assigned store_owner role\n";
    }
});

echo "Done for tenant {$tenantId}\n";
