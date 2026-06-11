<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AuthController;
use App\Http\Controllers\Tenant\BrandController;
use App\Http\Controllers\Tenant\CategoryController;
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
