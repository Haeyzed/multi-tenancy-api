<?php

namespace App\Providers;

use App\Enums\Central\UserRole;
use App\Events\Central\TenantOnboarded;
use App\Listeners\Central\BroadcastCentralTenantOnboarded;
use App\Listeners\Central\LogCentralLifecycleEvents;
use App\Listeners\Central\ProvisionTenantOwner;
use App\Listeners\Central\SendBillingNotifications;
use App\Models\Central\User as CentralUser;
use App\Models\Tenant\User as TenantUser;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes([
            'middleware' => ['auth:sanctum'],
            'prefix' => 'api/central',
        ]);

        Event::listen(TenantOnboarded::class, ProvisionTenantOwner::class);
        Event::listen(TenantOnboarded::class, BroadcastCentralTenantOnboarded::class);
        Event::subscribe(LogCentralLifecycleEvents::class);
        Event::subscribe(SendBillingNotifications::class);

        Gate::before(function (CentralUser|TenantUser|null $user, string $ability) {
            if ($user instanceof CentralUser && $user->hasRole(UserRole::SuperAdmin->value)) {
                return true;
            }

            return null;
        });

        Gate::define('viewApiDocs', function (CentralUser|TenantUser|null $user = null): bool {
            $user ??= auth()->user();

            if ($user instanceof CentralUser) {
                return $user->is_active && $user->can('platform.view');
            }

            if ($user instanceof TenantUser) {
                return $user->is_active && $user->can('staff.view');
            }

            return false;
        });
    }
}
