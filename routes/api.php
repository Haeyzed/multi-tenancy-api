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
        Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
        Route::post('auth/resend-otp', [AuthController::class, 'resendOtp'])->name('auth.resend-otp');
        Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])->name('auth.verify-otp');
        Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');

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
            Route::post('auth/change-password/otp', [AuthController::class, 'requestPasswordChangeOtp'])
                ->name('auth.change-password.otp');
            Route::post('auth/change-password', [AuthController::class, 'changePassword'])
                ->name('auth.change-password');

            /*
        |------------------------------------------------------------------
        | Users & Access Control
        |------------------------------------------------------------------
        */
            Route::get('users/metrics/cards', [UserController::class, 'metrics'])->name('users.metrics');
            Route::put('users/{user}/roles', [UserController::class, 'syncRoles'])->name('users.roles.sync');
            Route::put('users/{user}/permissions', [UserController::class, 'syncPermissions'])->name('users.permissions.sync');
            Route::delete('users/{user}/roles/{role}', [UserController::class, 'detachRole'])->name('users.roles.detach');
            Route::delete('users/{user}/permissions/{permission}', [UserController::class, 'detachPermission'])->name('users.permissions.detach');
            Route::apiResource('users', UserController::class);
            Route::post('users/{user}/login', [UserController::class, 'recordLogin'])->name('users.login');
            Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

            Route::get('roles/metrics/cards', [RoleController::class, 'metrics'])->name('roles.metrics');
            Route::get('roles/permissions/matrix', [RoleController::class, 'permissionsMatrix'])->name('roles.permissions.matrix');
            Route::put('roles/permissions/matrix', [RoleController::class, 'syncPermissionsMatrix'])->name('roles.permissions.matrix.sync');
            Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions.sync');
            Route::post('roles/{role}/permissions', [RoleController::class, 'attachPermissions'])->name('roles.permissions.attach');
            Route::delete('roles/{role}/permissions/{permission}', [RoleController::class, 'detachPermission'])->name('roles.permissions.detach');
            Route::apiResource('roles', RoleController::class);
            Route::get('permissions/metrics/cards', [PermissionController::class, 'metrics'])->name('permissions.metrics');
            Route::apiResource('permissions', PermissionController::class);

            /*
            |------------------------------------------------------------------
            | Plans & Features
            |------------------------------------------------------------------
            */
            Route::get('plans/metrics/cards', [PlanController::class, 'metrics'])->name('plans.metrics');
            Route::apiResource('plans', PlanController::class);
            Route::get('plans/options/list', [PlanController::class, 'options'])->name('plans.options');
            Route::apiResource('plan-features', PlanFeatureController::class);

            /*
            |------------------------------------------------------------------
            | Tenants & Configuration
            |------------------------------------------------------------------
            */
            Route::post('tenants/onboard', [TenantOnboardingController::class, 'store'])->name('tenants.onboard');
            Route::get('tenants/options/list', [TenantController::class, 'options'])->name('tenants.options');
            Route::get('tenants/metrics/cards', [TenantController::class, 'metrics'])->name('tenants.metrics');
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
            Route::get('subscriptions/metrics/cards', [SubscriptionController::class, 'metrics'])->name('subscriptions.metrics');
            Route::apiResource('subscriptions', SubscriptionController::class);
            Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
            Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
            Route::post('subscriptions/{subscription}/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');
            Route::post('subscriptions/{subscription}/downgrade', [SubscriptionController::class, 'downgrade'])->name('subscriptions.downgrade');
            Route::post('subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');

            Route::apiResource('subscription-items', SubscriptionItemController::class);
            Route::apiResource('subscription-events', SubscriptionEventController::class);

            Route::get('invoices/metrics/cards', [InvoiceController::class, 'metrics'])->name('invoices.metrics');
            Route::apiResource('invoices', InvoiceController::class);
            Route::get('invoices/overdue/list', [InvoiceController::class, 'getOverdue'])->name('invoices.overdue');
            Route::post('invoices/{invoice}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');

            Route::apiResource('invoice-items', InvoiceItemController::class);

            Route::get('payments/metrics/cards', [PaymentController::class, 'metrics'])->name('payments.metrics');
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
            Route::get('api-keys/metrics/cards', [ApiKeyController::class, 'metrics'])->name('api-keys.metrics');
            Route::apiResource('api-keys', ApiKeyController::class);
            Route::post('api-keys/{api_key}/usage', [ApiKeyController::class, 'recordUsage'])->name('api-keys.usage');
            Route::post('api-keys/{api_key}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');

            Route::get('health-checks/metrics/cards', [TenantHealthCheckController::class, 'metrics'])->name('health-checks.metrics');
            Route::apiResource('health-checks', TenantHealthCheckController::class);
            Route::get('metrics/cards', [TenantMetricController::class, 'metrics'])->name('metrics.cards');
            Route::apiResource('metrics', TenantMetricController::class);

            Route::get('support-tickets/metrics/cards', [TenantSupportTicketController::class, 'metrics'])->name('support-tickets.metrics');
            Route::apiResource('support-tickets', TenantSupportTicketController::class);
            Route::post('support-tickets/{support_ticket}/assign', [TenantSupportTicketController::class, 'assign'])->name('support-tickets.assign');
            Route::post('support-tickets/{support_ticket}/resolve', [TenantSupportTicketController::class, 'resolve'])->name('support-tickets.resolve');

            Route::apiResource('support-messages', TenantSupportMessageController::class);
            Route::post('support-messages/{support_message}/read', [TenantSupportMessageController::class, 'markAsRead'])->name('support-messages.read');

            Route::apiResource('impersonation-tokens', TenantImpersonationTokenController::class);
            Route::post('impersonation-tokens/{impersonation_token}/use', [TenantImpersonationTokenController::class, 'markAsUsed'])->name('impersonation-tokens.use');

            Route::get('error-logs/metrics/cards', [ErrorLogController::class, 'metrics'])->name('error-logs.metrics');
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
