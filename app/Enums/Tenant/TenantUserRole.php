<?php

declare(strict_types=1);

namespace App\Enums\Tenant;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;

/**
 * Default staff roles inside a tenant store.
 */
enum TenantUserRole: string implements HasLabel
{
    use InteractsWithEnum;

    case StoreOwner = 'store_owner';
    case Manager = 'manager';
    case Staff = 'staff';

    public const GUARD = 'tenant';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::StoreOwner => 'Store Owner',
            self::Manager => 'Manager',
            self::Staff => 'Staff',
        };
    }

    /**
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return [
            'dashboard.view',
            'catalog.view',
            'catalog.manage',
            'orders.view',
            'orders.manage',
            'inventory.view',
            'inventory.manage',
            'settings.view',
            'settings.manage',
            'staff.view',
            'staff.manage',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        $all = self::permissionNames();

        return [
            self::StoreOwner->value => $all,
            self::Manager->value => [
                'dashboard.view',
                'catalog.view',
                'catalog.manage',
                'orders.view',
                'orders.manage',
                'inventory.view',
                'inventory.manage',
                'settings.view',
            ],
            self::Staff->value => [
                'dashboard.view',
                'catalog.view',
                'orders.view',
                'orders.manage',
            ],
        ];
    }
}
