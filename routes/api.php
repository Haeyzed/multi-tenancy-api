<?php

declare(strict_types=1);

use App\Http\Controllers\Central\ActivityController;
use App\Http\Controllers\Central\ApiKeyController;
use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\DashboardController;
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
            | Dashboard
            |------------------------------------------------------------------
            */
            Route::middleware('permission:dashboard.view')->group(function (): void {
                Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
            });

            /*
            |------------------------------------------------------------------
            | Users & Access Control
            |------------------------------------------------------------------
            */
            Route::middleware('permission:users.view')->group(function (): void {
                Route::get('users/metrics/cards', [UserController::class, 'metrics'])->name('users.metrics');
                Route::get('users', [UserController::class, 'index'])->name('users.index');
                Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
            });

            Route::middleware('permission:users.create')->group(function (): void {
                Route::post('users', [UserController::class, 'store'])->name('users.store');
            });

            Route::middleware('permission:users.update')->group(function (): void {
                Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
                Route::put('users/{user}/roles', [UserController::class, 'syncRoles'])->name('users.roles.sync');
                Route::put('users/{user}/permissions', [UserController::class, 'syncPermissions'])->name('users.permissions.sync');
                Route::post('users/{user}/login', [UserController::class, 'recordLogin'])->name('users.login');
                Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
            });

            Route::middleware('permission:users.delete')->group(function (): void {
                Route::delete('users/bulk', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
                Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
                Route::delete('users/{user}/roles/{role}', [UserController::class, 'detachRole'])->name('users.roles.detach');
                Route::delete('users/{user}/permissions/{permission}', [UserController::class, 'detachPermission'])->name('users.permissions.detach');
            });

            Route::middleware('permission:roles.view')->group(function (): void {
                Route::get('roles/metrics/cards', [RoleController::class, 'metrics'])->name('roles.metrics');
                Route::get('roles/permissions/matrix', [RoleController::class, 'permissionsMatrix'])->name('roles.permissions.matrix');
                Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
                Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
            });

            Route::middleware('permission:roles.create')->group(function (): void {
                Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
            });

            Route::middleware('permission:roles.update')->group(function (): void {
                Route::put('roles/permissions/matrix', [RoleController::class, 'syncPermissionsMatrix'])->name('roles.permissions.matrix.sync');
                Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
                Route::put('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions.sync');
                Route::post('roles/{role}/permissions', [RoleController::class, 'attachPermissions'])->name('roles.permissions.attach');
            });

            Route::middleware('permission:roles.delete')->group(function (): void {
                Route::delete('roles/bulk', [RoleController::class, 'bulkDestroy'])->name('roles.bulk-destroy');
                Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
                Route::delete('roles/{role}/permissions/{permission}', [RoleController::class, 'detachPermission'])->name('roles.permissions.detach');
            });

            Route::middleware('permission:permissions.view')->group(function (): void {
                Route::get('permissions/metrics/cards', [PermissionController::class, 'metrics'])->name('permissions.metrics');
                Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
                Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
            });

            Route::middleware('permission:permissions.create')->group(function (): void {
                Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
            });

            Route::middleware('permission:permissions.update')->group(function (): void {
                Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
            });

            Route::middleware('permission:permissions.delete')->group(function (): void {
                Route::delete('permissions/bulk', [PermissionController::class, 'bulkDestroy'])->name('permissions.bulk-destroy');
                Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Plans & Features
            |------------------------------------------------------------------
            */
            Route::middleware('permission:billing.view')->group(function (): void {
                Route::get('plans/metrics/cards', [PlanController::class, 'metrics'])->name('plans.metrics');
                Route::get('plans/options/list', [PlanController::class, 'options'])->name('plans.options');
                Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
                Route::get('plans/{plan}', [PlanController::class, 'show'])->name('plans.show');
                Route::get('plan-features', [PlanFeatureController::class, 'index'])->name('plan-features.index');
                Route::get('plan-features/{plan_feature}', [PlanFeatureController::class, 'show'])->name('plan-features.show');
            });

            Route::middleware('permission:billing.manage')->group(function (): void {
                Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
                Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
                Route::delete('plans/bulk', [PlanController::class, 'bulkDestroy'])->name('plans.bulk-destroy');
                Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
                Route::post('plan-features', [PlanFeatureController::class, 'store'])->name('plan-features.store');
                Route::put('plan-features/{plan_feature}', [PlanFeatureController::class, 'update'])->name('plan-features.update');
                Route::delete('plan-features/{plan_feature}', [PlanFeatureController::class, 'destroy'])->name('plan-features.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Tenants & Configuration
            |------------------------------------------------------------------
            */
            Route::middleware('permission:tenants.view')->group(function (): void {
                Route::get('tenants/options/list', [TenantController::class, 'options'])->name('tenants.options');
                Route::get('tenants/metrics/cards', [TenantController::class, 'metrics'])->name('tenants.metrics');
                Route::get('tenants/status/{status}', [TenantController::class, 'getByStatus'])->name('tenants.by-status');
                Route::get('tenants/expiring/{days}', [TenantController::class, 'getExpiring'])->name('tenants.expiring');
                Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
                Route::get('tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
                Route::get('tenants/{tenant}/features', [TenantController::class, 'features'])->name('tenants.features');
                Route::get('domains', [DomainController::class, 'index'])->name('domains.index');
                Route::get('domains/{domain}', [DomainController::class, 'show'])->name('domains.show');
                Route::get('tenant-configs', [TenantConfigController::class, 'index'])->name('tenant-configs.index');
                Route::get('tenant-configs/{tenant_config}', [TenantConfigController::class, 'show'])->name('tenant-configs.show');
            });

            Route::middleware('permission:tenants.create')->group(function (): void {
                Route::post('tenants/onboard', [TenantOnboardingController::class, 'store'])->name('tenants.onboard');
                Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
                Route::post('domains', [DomainController::class, 'store'])->name('domains.store');
                Route::post('tenant-configs', [TenantConfigController::class, 'store'])->name('tenant-configs.store');
            });

            Route::middleware('permission:tenants.update')->group(function (): void {
                Route::put('tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
                Route::post('tenants/{tenant}/restore', [TenantController::class, 'restore'])->name('tenants.restore');
                Route::put('domains/{domain}', [DomainController::class, 'update'])->name('domains.update');
                Route::post('domains/{domain}/primary', [DomainController::class, 'setPrimary'])->name('domains.primary');
                Route::post('domains/{domain}/verify', [DomainController::class, 'verify'])->name('domains.verify');
                Route::put('tenant-configs/{tenant_config}', [TenantConfigController::class, 'update'])->name('tenant-configs.update');
            });

            Route::middleware('permission:tenants.delete')->group(function (): void {
                Route::delete('tenants/bulk', [TenantController::class, 'bulkDestroy'])->name('tenants.bulk-destroy');
                Route::delete('tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
                Route::delete('tenants/{tenant}/force', [TenantController::class, 'forceDestroy'])->name('tenants.force-delete');
                Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');
                Route::delete('tenant-configs/{tenant_config}', [TenantConfigController::class, 'destroy'])->name('tenant-configs.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Subscriptions & Billing
            |------------------------------------------------------------------
            */
            Route::middleware('permission:billing.view')->group(function (): void {
                Route::get('subscriptions/metrics/cards', [SubscriptionController::class, 'metrics'])->name('subscriptions.metrics');
                Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
                Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
                Route::get('subscription-items', [SubscriptionItemController::class, 'index'])->name('subscription-items.index');
                Route::get('subscription-items/{subscription_item}', [SubscriptionItemController::class, 'show'])->name('subscription-items.show');
                Route::get('subscription-events', [SubscriptionEventController::class, 'index'])->name('subscription-events.index');
                Route::get('subscription-events/{subscription_event}', [SubscriptionEventController::class, 'show'])->name('subscription-events.show');
                Route::get('invoices/metrics/cards', [InvoiceController::class, 'metrics'])->name('invoices.metrics');
                Route::get('invoices/overdue/list', [InvoiceController::class, 'getOverdue'])->name('invoices.overdue');
                Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
                Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
                Route::get('invoice-items', [InvoiceItemController::class, 'index'])->name('invoice-items.index');
                Route::get('invoice-items/{invoice_item}', [InvoiceItemController::class, 'show'])->name('invoice-items.show');
                Route::get('payments/metrics/cards', [PaymentController::class, 'metrics'])->name('payments.metrics');
                Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
                Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
                Route::get('payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
                Route::get('payment-methods/{payment_method}', [PaymentMethodController::class, 'show'])->name('payment-methods.show');
                Route::get('usage-records', [UsageRecordController::class, 'index'])->name('usage-records.index');
                Route::get('usage-records/{usage_record}', [UsageRecordController::class, 'show'])->name('usage-records.show');
            });

            Route::middleware('permission:billing.manage')->group(function (): void {
                Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
                Route::put('subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
                Route::delete('subscriptions/bulk', [SubscriptionController::class, 'bulkDestroy'])->name('subscriptions.bulk-destroy');
                Route::delete('subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
                Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
                Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
                Route::post('subscriptions/{subscription}/upgrade', [SubscriptionController::class, 'upgrade'])->name('subscriptions.upgrade');
                Route::post('subscriptions/{subscription}/downgrade', [SubscriptionController::class, 'downgrade'])->name('subscriptions.downgrade');
                Route::post('subscriptions/{subscription}/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
                Route::post('subscription-items', [SubscriptionItemController::class, 'store'])->name('subscription-items.store');
                Route::put('subscription-items/{subscription_item}', [SubscriptionItemController::class, 'update'])->name('subscription-items.update');
                Route::delete('subscription-items/{subscription_item}', [SubscriptionItemController::class, 'destroy'])->name('subscription-items.destroy');
                Route::post('subscription-events', [SubscriptionEventController::class, 'store'])->name('subscription-events.store');
                Route::put('subscription-events/{subscription_event}', [SubscriptionEventController::class, 'update'])->name('subscription-events.update');
                Route::delete('subscription-events/{subscription_event}', [SubscriptionEventController::class, 'destroy'])->name('subscription-events.destroy');
                Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
                Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
                Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
                Route::post('invoices/{invoice}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');
                Route::post('invoice-items', [InvoiceItemController::class, 'store'])->name('invoice-items.store');
                Route::put('invoice-items/{invoice_item}', [InvoiceItemController::class, 'update'])->name('invoice-items.update');
                Route::delete('invoice-items/{invoice_item}', [InvoiceItemController::class, 'destroy'])->name('invoice-items.destroy');
                Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
                Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
                Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
                Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
                Route::post('payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
                Route::put('payment-methods/{payment_method}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
                Route::delete('payment-methods/{payment_method}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
                Route::post('payment-methods/{payment_method}/default', [PaymentMethodController::class, 'setDefault'])->name('payment-methods.default');
                Route::post('usage-records', [UsageRecordController::class, 'store'])->name('usage-records.store');
                Route::put('usage-records/{usage_record}', [UsageRecordController::class, 'update'])->name('usage-records.update');
                Route::delete('usage-records/{usage_record}', [UsageRecordController::class, 'destroy'])->name('usage-records.destroy');
            });

            /*
            |------------------------------------------------------------------
            | Tenant Operations & Monitoring
            |------------------------------------------------------------------
            */
            Route::middleware('permission:api-keys.view')->group(function (): void {
                Route::get('api-keys/metrics/cards', [ApiKeyController::class, 'metrics'])->name('api-keys.metrics');
                Route::get('api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
                Route::get('api-keys/{api_key}', [ApiKeyController::class, 'show'])->name('api-keys.show');
            });

            Route::middleware('permission:api-keys.manage')->group(function (): void {
                Route::post('api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
                Route::put('api-keys/{api_key}', [ApiKeyController::class, 'update'])->name('api-keys.update');
                Route::delete('api-keys/{api_key}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
                Route::post('api-keys/{api_key}/usage', [ApiKeyController::class, 'recordUsage'])->name('api-keys.usage');
                Route::post('api-keys/{api_key}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');
            });

            Route::middleware('permission:monitoring.view')->group(function (): void {
                Route::get('health-checks/metrics/cards', [TenantHealthCheckController::class, 'metrics'])->name('health-checks.metrics');
                Route::get('health-checks', [TenantHealthCheckController::class, 'index'])->name('health-checks.index');
                Route::get('health-checks/{health_check}', [TenantHealthCheckController::class, 'show'])->name('health-checks.show');
                Route::get('metrics/cards', [TenantMetricController::class, 'metrics'])->name('metrics.cards');
                Route::get('metrics', [TenantMetricController::class, 'index'])->name('metrics.index');
                Route::get('metrics/{metric}', [TenantMetricController::class, 'show'])->name('metrics.show');
                Route::get('error-logs/metrics/cards', [ErrorLogController::class, 'metrics'])->name('error-logs.metrics');
                Route::get('error-logs', [ErrorLogController::class, 'index'])->name('error-logs.index');
                Route::get('error-logs/{error_log}', [ErrorLogController::class, 'show'])->name('error-logs.show');
            });

            Route::middleware('permission:monitoring.manage')->group(function (): void {
                Route::post('health-checks', [TenantHealthCheckController::class, 'store'])->name('health-checks.store');
                Route::put('health-checks/{health_check}', [TenantHealthCheckController::class, 'update'])->name('health-checks.update');
                Route::delete('health-checks/{health_check}', [TenantHealthCheckController::class, 'destroy'])->name('health-checks.destroy');
                Route::post('metrics', [TenantMetricController::class, 'store'])->name('metrics.store');
                Route::put('metrics/{metric}', [TenantMetricController::class, 'update'])->name('metrics.update');
                Route::delete('metrics/{metric}', [TenantMetricController::class, 'destroy'])->name('metrics.destroy');
                Route::post('error-logs', [ErrorLogController::class, 'store'])->name('error-logs.store');
                Route::put('error-logs/{error_log}', [ErrorLogController::class, 'update'])->name('error-logs.update');
                Route::delete('error-logs/{error_log}', [ErrorLogController::class, 'destroy'])->name('error-logs.destroy');
                Route::post('error-logs/{error_log}/resolve', [ErrorLogController::class, 'resolve'])->name('error-logs.resolve');
            });

            Route::middleware('permission:support.view')->group(function (): void {
                Route::get('support-tickets/metrics/cards', [TenantSupportTicketController::class, 'metrics'])->name('support-tickets.metrics');
                Route::get('support-tickets', [TenantSupportTicketController::class, 'index'])->name('support-tickets.index');
                Route::get('support-tickets/{support_ticket}', [TenantSupportTicketController::class, 'show'])->name('support-tickets.show');
                Route::get('support-messages', [TenantSupportMessageController::class, 'index'])->name('support-messages.index');
                Route::get('support-messages/{support_message}', [TenantSupportMessageController::class, 'show'])->name('support-messages.show');
            });

            Route::middleware('permission:support.manage')->group(function (): void {
                Route::post('support-tickets', [TenantSupportTicketController::class, 'store'])->name('support-tickets.store');
                Route::put('support-tickets/{support_ticket}', [TenantSupportTicketController::class, 'update'])->name('support-tickets.update');
                Route::delete('support-tickets/{support_ticket}', [TenantSupportTicketController::class, 'destroy'])->name('support-tickets.destroy');
                Route::post('support-tickets/{support_ticket}/assign', [TenantSupportTicketController::class, 'assign'])->name('support-tickets.assign');
                Route::post('support-tickets/{support_ticket}/resolve', [TenantSupportTicketController::class, 'resolve'])->name('support-tickets.resolve');
                Route::post('support-messages', [TenantSupportMessageController::class, 'store'])->name('support-messages.store');
                Route::put('support-messages/{support_message}', [TenantSupportMessageController::class, 'update'])->name('support-messages.update');
                Route::delete('support-messages/{support_message}', [TenantSupportMessageController::class, 'destroy'])->name('support-messages.destroy');
                Route::post('support-messages/{support_message}/read', [TenantSupportMessageController::class, 'markAsRead'])->name('support-messages.read');
            });

            Route::middleware('permission:impersonation.use')->group(function (): void {
                Route::get('impersonation-tokens', [TenantImpersonationTokenController::class, 'index'])->name('impersonation-tokens.index');
                Route::get('impersonation-tokens/{impersonation_token}', [TenantImpersonationTokenController::class, 'show'])->name('impersonation-tokens.show');
                Route::post('impersonation-tokens', [TenantImpersonationTokenController::class, 'store'])->name('impersonation-tokens.store');
                Route::put('impersonation-tokens/{impersonation_token}', [TenantImpersonationTokenController::class, 'update'])->name('impersonation-tokens.update');
                Route::delete('impersonation-tokens/{impersonation_token}', [TenantImpersonationTokenController::class, 'destroy'])->name('impersonation-tokens.destroy');
                Route::post('impersonation-tokens/{impersonation_token}/use', [TenantImpersonationTokenController::class, 'markAsUsed'])->name('impersonation-tokens.use');
            });

            /*
            |------------------------------------------------------------------
            | Platform Content & Audit
            |------------------------------------------------------------------
            */
            Route::middleware('permission:platform.view')->group(function (): void {
                Route::get('announcements/metrics/cards', [PlatformAnnouncementController::class, 'metrics'])->name('announcements.metrics');
                Route::get('announcements', [PlatformAnnouncementController::class, 'index'])->name('announcements.index');
                Route::get('announcements/{announcement}', [PlatformAnnouncementController::class, 'show'])->name('announcements.show');
                Route::get('changelog', [PlatformChangelogController::class, 'index'])->name('changelog.index');
                Route::get('changelog/{changelog}', [PlatformChangelogController::class, 'show'])->name('changelog.show');
                Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
                Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
            });

            Route::middleware('permission:platform.manage')->group(function (): void {
                Route::post('announcements', [PlatformAnnouncementController::class, 'store'])->name('announcements.store');
                Route::put('announcements/{announcement}', [PlatformAnnouncementController::class, 'update'])->name('announcements.update');
                Route::delete('announcements/bulk', [PlatformAnnouncementController::class, 'bulkDestroy'])->name('announcements.bulk-destroy');
                Route::delete('announcements/{announcement}', [PlatformAnnouncementController::class, 'destroy'])->name('announcements.destroy');
                Route::post('changelog', [PlatformChangelogController::class, 'store'])->name('changelog.store');
                Route::put('changelog/{changelog}', [PlatformChangelogController::class, 'update'])->name('changelog.update');
                Route::delete('changelog/{changelog}', [PlatformChangelogController::class, 'destroy'])->name('changelog.destroy');
                Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
                Route::put('activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
                Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
            });

        });

    });
