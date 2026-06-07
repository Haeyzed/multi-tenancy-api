<?php

namespace App\Providers;

use App\Listeners\Central\LogCentralLifecycleEvents;
use App\Listeners\Central\SendBillingNotifications;
use App\Enums\Central\UserRole;
use App\Models\Central\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
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
        Event::subscribe(LogCentralLifecycleEvents::class);
        Event::subscribe(SendBillingNotifications::class);

        Gate::before(function (?User $user, string $ability) {
            if ($user?->hasRole(UserRole::SuperAdmin->value)) {
                return true;
            }

            return null;
        });

        Gate::define('viewApiDocs', function (?User $user = null): bool {
            $user ??= auth()->user();

            if (! $user instanceof User) {
                return false;
            }

            return $user->is_active && $user->can('platform.view');
        });

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->info->title = config('app.name').' Central API';
            });
    }
}
