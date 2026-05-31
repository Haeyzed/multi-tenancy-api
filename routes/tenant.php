<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant API Routes
|--------------------------------------------------------------------------
|
| Loaded on tenant domains only. Middleware is defined in config/tenancy.php
| (`middleware` key) and uses the `api` stack with tenant identification.
|
| Additional per-route middleware:
| - plan.feature:{key} — gate routes by plan entitlement (e.g. api_access).
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

        Route::get('/user', function (Request $request) {
            return $request->user();
        })->middleware('auth:sanctum');

        Route::middleware('plan.feature:api_access')->group(function (): void {
            Route::get('/integrations', function () {
                return [
                    'message' => 'API integrations endpoint',
                    'tenant_id' => tenant('id'),
                ];
            });
        });
    });
