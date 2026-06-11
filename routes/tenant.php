<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\BootstrapController;
use App\Http\Controllers\Tenant\BrandController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Controllers\Tenant\GeneralSettingController;
use App\Http\Controllers\Tenant\MediaController;
use App\Http\Controllers\Tenant\MediaLibraryFolderController;
use App\Http\Controllers\Tenant\PermissionController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\RoleController;
use App\Http\Controllers\Tenant\StoreAddressController;
use App\Http\Controllers\Tenant\StoreController;
use App\Http\Controllers\Tenant\StoreSettingController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant API Routes
|--------------------------------------------------------------------------
|
| Loaded on tenant domains only. Middleware is defined in config/tenancy.php
| (`middleware` key) and uses the `api` stack with tenant identification.
|
*/

Route::middleware(config('tenancy.middleware'))
    ->prefix('api')
    ->group(function (): void {
        Route::get('/', function () {
            return [
                'message' => 'Tenant API',
                'tenant_id' => tenant('id'),
            ];
        });

        Route::get('bootstrap', [BootstrapController::class, 'show'])->name('tenant.bootstrap.show');

        /*
        |------------------------------------------------------------------
        | Authentication (public)
        |------------------------------------------------------------------
        */
        Route::post('auth/login', [AuthController::class, 'login'])->name('tenant.auth.login');
        Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('tenant.auth.forgot-password');
        Route::post('auth/resend-otp', [AuthController::class, 'resendOtp'])->name('tenant.auth.resend-otp');
        Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('tenant.auth.verify-otp');
        Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->name('tenant.auth.reset-password');

        Route::middleware(['auth:sanctum'])->group(function (): void {
            Route::post('auth/logout', [AuthController::class, 'logout'])->name('tenant.auth.logout');
            Route::get('auth/me', [AuthController::class, 'me'])->name('tenant.auth.me');
            Route::post('auth/change-password/otp', [AuthController::class, 'requestPasswordChangeOtp'])
                ->name('tenant.auth.change-password.otp');
            Route::post('auth/change-password', [AuthController::class, 'changePassword'])
                ->name('tenant.auth.change-password');

            /*
            |------------------------------------------------------------------
            | Catalog — Brands
            |------------------------------------------------------------------
            */
            Route::middleware('permission:catalog.view,tenant')->group(function (): void {
                Route::get('brands/metrics/cards', [BrandController::class, 'metrics'])->name('tenant.brands.metrics');
                Route::get('brands/options/list', [BrandController::class, 'options'])->name('tenant.brands.options');
                Route::get('brands', [BrandController::class, 'index'])->name('tenant.brands.index');
                Route::get('brands/{brand}', [BrandController::class, 'show'])->name('tenant.brands.show');
            });

            Route::middleware('permission:catalog.manage,tenant')->group(function (): void {
                Route::post('brands', [BrandController::class, 'store'])->name('tenant.brands.store');
                Route::put('brands/{brand}', [BrandController::class, 'update'])->name('tenant.brands.update');
                Route::delete('brands/bulk', [BrandController::class, 'bulkDestroy'])->name('tenant.brands.bulk-destroy');
                Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('tenant.brands.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Catalog — Categories
            |------------------------------------------------------------------
            */
            Route::middleware('permission:catalog.view,tenant')->group(function (): void {
                Route::get('categories/metrics/cards', [CategoryController::class, 'metrics'])->name('tenant.categories.metrics');
                Route::get('categories/options/list', [CategoryController::class, 'options'])->name('tenant.categories.options');
                Route::get('categories', [CategoryController::class, 'index'])->name('tenant.categories.index');
                Route::get('categories/{category}', [CategoryController::class, 'show'])->name('tenant.categories.show');
            });

            Route::middleware('permission:catalog.manage,tenant')->group(function (): void {
                Route::post('categories', [CategoryController::class, 'store'])->name('tenant.categories.store');
                Route::put('categories/{category}', [CategoryController::class, 'update'])->name('tenant.categories.update');
                Route::delete('categories/bulk', [CategoryController::class, 'bulkDestroy'])->name('tenant.categories.bulk-destroy');
                Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('tenant.categories.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Catalog — Products
            |------------------------------------------------------------------
            */
            Route::middleware('permission:catalog.view,tenant')->group(function (): void {
                Route::get('products/metrics/cards', [ProductController::class, 'metrics'])->name('tenant.products.metrics');
                Route::get('products/options/list', [ProductController::class, 'options'])->name('tenant.products.options');
                Route::get('products', [ProductController::class, 'index'])->name('tenant.products.index');
                Route::get('products/{product}', [ProductController::class, 'show'])->name('tenant.products.show');
            });

            Route::middleware('permission:catalog.manage,tenant')->group(function (): void {
                Route::post('products', [ProductController::class, 'store'])->name('tenant.products.store');
                Route::put('products/{product}', [ProductController::class, 'update'])->name('tenant.products.update');
                Route::delete('products/bulk', [ProductController::class, 'bulkDestroy'])->name('tenant.products.bulk-destroy');
                Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('tenant.products.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Staff — Users
            |------------------------------------------------------------------
            */
            Route::middleware('permission:staff.view,tenant')->group(function (): void {
                Route::get('users/metrics/cards', [UserController::class, 'metrics'])->name('tenant.users.metrics');
                Route::get('users', [UserController::class, 'index'])->name('tenant.users.index');
                Route::get('users/{user}', [UserController::class, 'show'])->name('tenant.users.show');
            });

            Route::middleware('permission:staff.manage,tenant')->group(function (): void {
                Route::post('users', [UserController::class, 'store'])->name('tenant.users.store');
                Route::put('users/{user}', [UserController::class, 'update'])->name('tenant.users.update');
                Route::put('users/{user}/roles', [UserController::class, 'syncRoles'])->name('tenant.users.roles.sync');
                Route::put('users/{user}/permissions', [UserController::class, 'syncPermissions'])->name('tenant.users.permissions.sync');
                Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('tenant.users.toggle-active');
                Route::delete('users/bulk', [UserController::class, 'bulkDestroy'])->name('tenant.users.bulk-destroy');
                Route::delete('users/{user}', [UserController::class, 'destroy'])->name('tenant.users.destroy');
                Route::delete('users/{user}/roles/{role}', [UserController::class, 'detachRole'])->name('tenant.users.roles.detach');
                Route::delete('users/{user}/permissions/{permission}', [UserController::class, 'detachPermission'])->name('tenant.users.permissions.detach');
            });

            /*
            |------------------------------------------------------------------
            | Staff — Roles
            |------------------------------------------------------------------
            */
            Route::middleware('permission:staff.view,tenant')->group(function (): void {
                Route::get('roles/metrics/cards', [RoleController::class, 'metrics'])->name('tenant.roles.metrics');
                Route::get('roles/permissions/matrix', [RoleController::class, 'permissionsMatrix'])->name('tenant.roles.permissions.matrix');
                Route::get('roles', [RoleController::class, 'index'])->name('tenant.roles.index');
                Route::get('roles/{role}', [RoleController::class, 'show'])->name('tenant.roles.show');
            });

            Route::middleware('permission:staff.manage,tenant')->group(function (): void {
                Route::post('roles', [RoleController::class, 'store'])->name('tenant.roles.store');
                Route::put('roles/permissions/matrix', [RoleController::class, 'syncPermissionsMatrix'])->name('tenant.roles.permissions.matrix.sync');
                Route::put('roles/{role}', [RoleController::class, 'update'])->name('tenant.roles.update');
                Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('tenant.roles.permissions.sync');
                Route::post('roles/{role}/permissions', [RoleController::class, 'attachPermissions'])->name('tenant.roles.permissions.attach');
                Route::delete('roles/bulk', [RoleController::class, 'bulkDestroy'])->name('tenant.roles.bulk-destroy');
                Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('tenant.roles.destroy');
                Route::delete('roles/{role}/permissions/{permission}', [RoleController::class, 'detachPermission'])->name('tenant.roles.permissions.detach');
            });

            /*
            |------------------------------------------------------------------
            | Staff — Permissions
            |------------------------------------------------------------------
            */
            Route::middleware('permission:staff.view,tenant')->group(function (): void {
                Route::get('permissions/metrics/cards', [PermissionController::class, 'metrics'])->name('tenant.permissions.metrics');
                Route::get('permissions', [PermissionController::class, 'index'])->name('tenant.permissions.index');
                Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('tenant.permissions.show');
            });

            Route::middleware('permission:staff.manage,tenant')->group(function (): void {
                Route::post('permissions', [PermissionController::class, 'store'])->name('tenant.permissions.store');
                Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('tenant.permissions.update');
                Route::delete('permissions/bulk', [PermissionController::class, 'bulkDestroy'])->name('tenant.permissions.bulk-destroy');
                Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('tenant.permissions.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Settings — General (tenant-wide)
            |------------------------------------------------------------------
            */
            Route::middleware('permission:settings.view,tenant')->group(function (): void {
                Route::get('general-settings/metrics/cards', [GeneralSettingController::class, 'metrics'])->name('tenant.general-settings.metrics');
                Route::get('general-settings', [GeneralSettingController::class, 'show'])->name('tenant.general-settings.show');
            });

            Route::middleware('permission:settings.manage,tenant')->group(function (): void {
                Route::put('general-settings', [GeneralSettingController::class, 'update'])->name('tenant.general-settings.update');
            });

            /*
            |------------------------------------------------------------------
            | Settings — Stores
            |------------------------------------------------------------------
            */
            Route::middleware('permission:settings.view,tenant')->group(function (): void {
                Route::get('stores/metrics/cards', [StoreController::class, 'metrics'])->name('tenant.stores.metrics');
                Route::get('stores/options/list', [StoreController::class, 'options'])->name('tenant.stores.options');
                Route::get('stores', [StoreController::class, 'index'])->name('tenant.stores.index');
                Route::get('stores/{store}', [StoreController::class, 'show'])->name('tenant.stores.show');
                Route::get('stores/{store}/settings', [StoreSettingController::class, 'show'])->name('tenant.stores.settings.show');
                Route::get('stores/{store}/addresses', [StoreAddressController::class, 'index'])->name('tenant.stores.addresses.index');
                Route::get('stores/{store}/addresses/{address}', [StoreAddressController::class, 'show'])->name('tenant.stores.addresses.show');
            });

            Route::middleware('permission:settings.manage,tenant')->group(function (): void {
                Route::post('stores', [StoreController::class, 'store'])->name('tenant.stores.store');
                Route::put('stores/{store}', [StoreController::class, 'update'])->name('tenant.stores.update');
                Route::put('stores/{store}/settings', [StoreSettingController::class, 'update'])->name('tenant.stores.settings.update');
                Route::post('stores/{store}/toggle-active', [StoreController::class, 'toggleActive'])->name('tenant.stores.toggle-active');
                Route::post('stores/{store}/set-primary', [StoreController::class, 'setPrimary'])->name('tenant.stores.set-primary');
                Route::post('stores/{store}/addresses', [StoreAddressController::class, 'store'])->name('tenant.stores.addresses.store');
                Route::put('stores/{store}/addresses/{address}', [StoreAddressController::class, 'update'])->name('tenant.stores.addresses.update');
                Route::post('stores/{store}/addresses/{address}/set-default', [StoreAddressController::class, 'setDefault'])->name('tenant.stores.addresses.set-default');
                Route::delete('stores/{store}/addresses/bulk', [StoreAddressController::class, 'bulkDestroy'])->name('tenant.stores.addresses.bulk-destroy');
                Route::delete('stores/{store}/addresses/{address}', [StoreAddressController::class, 'destroy'])->name('tenant.stores.addresses.destroy');
                Route::delete('stores/bulk', [StoreController::class, 'bulkDestroy'])->name('tenant.stores.bulk-destroy');
                Route::delete('stores/{store}', [StoreController::class, 'destroy'])->name('tenant.stores.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Inventory — Warehouses
            |------------------------------------------------------------------
            */
            Route::middleware('permission:inventory.view,tenant')->group(function (): void {
                Route::get('warehouses/metrics/cards', [WarehouseController::class, 'metrics'])->name('tenant.warehouses.metrics');
                Route::get('warehouses/options/list', [WarehouseController::class, 'options'])->name('tenant.warehouses.options');
                Route::get('warehouses', [WarehouseController::class, 'index'])->name('tenant.warehouses.index');
                Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('tenant.warehouses.show');
            });

            Route::middleware('permission:inventory.manage,tenant')->group(function (): void {
                Route::post('warehouses', [WarehouseController::class, 'store'])->name('tenant.warehouses.store');
                Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('tenant.warehouses.update');
                Route::post('warehouses/{warehouse}/toggle-active', [WarehouseController::class, 'toggleActive'])->name('tenant.warehouses.toggle-active');
                Route::delete('warehouses/bulk', [WarehouseController::class, 'bulkDestroy'])->name('tenant.warehouses.bulk-destroy');
                Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('tenant.warehouses.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Media library
            |------------------------------------------------------------------
            */
            Route::middleware('permission:settings.view,tenant')->group(function (): void {
                Route::get('media/metrics/cards', [MediaController::class, 'metrics'])->name('tenant.media.metrics');
                Route::get('media', [MediaController::class, 'index'])->name('tenant.media.index');
                Route::get('media/{media}', [MediaController::class, 'show'])->name('tenant.media.show');
                Route::get('media-folders/tree', [MediaLibraryFolderController::class, 'tree'])->name('tenant.media-folders.tree');
                Route::get('media-folders', [MediaLibraryFolderController::class, 'index'])->name('tenant.media-folders.index');
                Route::get('media-folders/{folder}', [MediaLibraryFolderController::class, 'show'])->name('tenant.media-folders.show');
            });

            Route::middleware('permission:settings.manage,tenant')->group(function (): void {
                Route::post('media', [MediaController::class, 'store'])->name('tenant.media.store');
                Route::put('media/{media}', [MediaController::class, 'update'])->name('tenant.media.update');
                Route::delete('media/bulk', [MediaController::class, 'bulkDestroy'])->name('tenant.media.bulk-destroy');
                Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('tenant.media.destroy');
                Route::post('media-folders', [MediaLibraryFolderController::class, 'store'])->name('tenant.media-folders.store');
                Route::put('media-folders/{folder}', [MediaLibraryFolderController::class, 'update'])->name('tenant.media-folders.update');
                Route::delete('media-folders/bulk', [MediaLibraryFolderController::class, 'bulkDestroy'])->name('tenant.media-folders.bulk-destroy');
                Route::delete('media-folders/{folder}', [MediaLibraryFolderController::class, 'destroy'])->name('tenant.media-folders.destroy');
            });
        });

        Route::middleware('plan.feature:api_access')->group(function (): void {
            Route::get('/integrations', function () {
                return [
                    'message' => 'API integrations endpoint',
                    'tenant_id' => tenant('id'),
                ];
            });
        });
    });
