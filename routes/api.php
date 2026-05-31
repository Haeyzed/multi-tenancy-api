<?php

declare(strict_types=1);

use App\Http\Controllers\Central\ActivityController;
use App\Http\Controllers\Central\ApiKeyController;
use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\DomainController;
use App\Http\Controllers\Central\ErrorLogController;
use App\Http\Controllers\Central\InvoiceController;
use App\Http\Controllers\Central\InvoiceItemController;
use App\Http\Controllers\Central\PaymentConfigController;
use App\Http\Controllers\Central\PaymentController;
use App\Http\Controllers\Central\PaymentMethodController;
use App\Http\Controllers\Central\PaystackCallbackController;
use App\Http\Controllers\Central\PaystackWebhookController;
use App\Http\Controllers\Central\PermissionController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\PlanFeatureController;
use App\Http\Controllers\Central\PlatformAnnouncementController;
use App\Http\Controllers\Central\PlatformChangelogController;
use App\Http\Controllers\Central\PublicPlanController;
use App\Http\Controllers\Central\RoleController;
use App\Http\Controllers\Central\SignupController;
use App\Http\Controllers\Central\StripeWebhookController;
use App\Http\Controllers\Central\SubscriptionController;
use App\Http\Controllers\Central\SubscriptionEventController;
use App\Http\Controllers\Central\SubscriptionItemController;
use App\Http\Controllers\Central\TenantConfigController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantHealthCheckController;
use App\Http\Controllers\Central\TenantImpersonationTokenController;
use App\Http\Controllers\Central\TenantMetricController;
use App\Http\Controllers\Central\TenantOnboardingController;
use App\Http\Controllers\Central\TenantSupportMessageController;
use App\Http\Controllers\Central\TenantSupportTicketController;
use App\Http\Controllers\Central\UsageRecordController;
use App\Http\Controllers\Central\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Central API Routes
|--------------------------------------------------------------------------
|
| Platform-level operations against the central database.
| Base URL: /api/central/*
| Authentication: Sanctum (auth:sanctum) — login is public; all other routes require a token.
|
| Route parameters use snake_case and resolve to Eloquent models via
| implicit route model binding (e.g. {user} → User, {tenant} → Tenant).
|
*/

Route::prefix('central')
    ->name('central.')
    ->group(function (): void {

        /*
        |------------------------------------------------------------------
        | Authentication
        |------------------------------------------------------------------
        */
        Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

        /*
        |------------------------------------------------------------------
        | Public Self-Service Signup & Payments
        |------------------------------------------------------------------
        */
        Route::get('plans/public', [PublicPlanController::class, 'index'])->name('plans.public');
        Route::get('payments/config', [PaymentConfigController::class, 'index'])->name('payments.config');
        Route::post('signup', [SignupController::class, 'store'])->name('signup.store');
        Route::post('signup/{tenant}/checkout', [SignupController::class, 'checkout'])->name('signup.checkout');

        Route::get('payments/paystack/callback', PaystackCallbackController::class)
            ->name('payments.paystack.callback');

        Route::post('webhooks/stripe', [StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
        Route::post('webhooks/paystack', [PaystackWebhookController::class, 'handle'])->name('webhooks.paystack');

        Route::middleware(['auth:sanctum'])->group(function (): void {

            Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');

            /*
        |------------------------------------------------------------------
        | Users & Access Control
        |------------------------------------------------------------------
        */
            Route::apiResource('users', UserController::class);
            Route::post('users/{user}/login', [UserController::class, 'recordLogin'])->name('users.login');
            Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

            Route::apiResource('roles', RoleController::class);
            Route::apiResource('permissions', PermissionController::class);

            /*
            |------------------------------------------------------------------
            | Plans & Features
            |------------------------------------------------------------------
            */
            Route::apiResource('plans', PlanController::class);
            Route::apiResource('plan-features', PlanFeatureController::class);

            /*
            |------------------------------------------------------------------
            | Tenants & Configuration
            |------------------------------------------------------------------
            */
            Route::post('tenants/onboard', [TenantOnboardingController::class, 'store'])->name('tenants.onboard');
            Route::get('tenants/status/{status}', [TenantController::class, 'getByStatus'])->name('tenants.by-status');
            Route::get('tenants/expiring/{days}', [TenantController::class, 'getExpiring'])->name('tenants.expiring');
            Route::apiResource('tenants', TenantController::class);
            Route::get('tenants/{tenant}/features', [TenantController::class, 'features'])->name('tenants.features');
            Route::post('tenants/{tenant}/restore', [TenantController::class, 'restore'])->name('tenants.restore');
            Route::delete('tenants/{tenant}/force', [TenantController::class, 'forceDestroy'])->name('tenants.force-delete');

            Route::apiResource('domains', DomainController::class);
            Route::post('domains/{domain}/primary', [DomainController::class, 'setPrimary'])->name('domains.primary');
            Route::post('domains/{domain}/verify', [DomainController::class, 'verify'])->name('domains.verify');

            Route::apiResource('tenant-configs', TenantConfigController::class);

            /*
            |------------------------------------------------------------------
            | Subscriptions & Billing
            |------------------------------------------------------------------
            */
            Route::apiResource('subscriptions', SubscriptionController::class);
            Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
            Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
            Route::post('subscriptions/{subscription}/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');
            Route::post('subscriptions/{subscription}/downgrade', [SubscriptionController::class, 'downgrade'])->name('subscriptions.downgrade');
            Route::post('subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');

            Route::apiResource('subscription-items', SubscriptionItemController::class);
            Route::apiResource('subscription-events', SubscriptionEventController::class);

            Route::apiResource('invoices', InvoiceController::class);
            Route::get('invoices/overdue/list', [InvoiceController::class, 'getOverdue'])->name('invoices.overdue');
            Route::post('invoices/{invoice}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');

            Route::apiResource('invoice-items', InvoiceItemController::class);

            Route::apiResource('payments', PaymentController::class);
            Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

            Route::apiResource('payment-methods', PaymentMethodController::class);
            Route::post('payment-methods/{payment_method}/default', [PaymentMethodController::class, 'setDefault'])->name('payment-methods.default');

            Route::apiResource('usage-records', UsageRecordController::class);

            /*
            |------------------------------------------------------------------
            | Tenant Operations & Monitoring
            |------------------------------------------------------------------
            */
            Route::apiResource('api-keys', ApiKeyController::class);
            Route::post('api-keys/{api_key}/usage', [ApiKeyController::class, 'recordUsage'])->name('api-keys.usage');
            Route::post('api-keys/{api_key}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');

            Route::apiResource('health-checks', TenantHealthCheckController::class);
            Route::apiResource('metrics', TenantMetricController::class);

            Route::apiResource('support-tickets', TenantSupportTicketController::class);
            Route::post('support-tickets/{support_ticket}/assign', [TenantSupportTicketController::class, 'assign'])->name('support-tickets.assign');
            Route::post('support-tickets/{support_ticket}/resolve', [TenantSupportTicketController::class, 'resolve'])->name('support-tickets.resolve');

            Route::apiResource('support-messages', TenantSupportMessageController::class);
            Route::post('support-messages/{support_message}/read', [TenantSupportMessageController::class, 'markAsRead'])->name('support-messages.read');

            Route::apiResource('impersonation-tokens', TenantImpersonationTokenController::class);
            Route::post('impersonation-tokens/{impersonation_token}/use', [TenantImpersonationTokenController::class, 'markAsUsed'])->name('impersonation-tokens.use');

            Route::apiResource('error-logs', ErrorLogController::class);
            Route::post('error-logs/{error_log}/resolve', [ErrorLogController::class, 'resolve'])->name('error-logs.resolve');

            /*
            |------------------------------------------------------------------
            | Platform Content & Audit
            |------------------------------------------------------------------
            */
            Route::apiResource('announcements', PlatformAnnouncementController::class);
            Route::apiResource('changelog', PlatformChangelogController::class);
            Route::apiResource('activities', ActivityController::class);

        });

    });
