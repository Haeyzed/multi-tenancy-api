<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Orchestrates central database seeding in dependency order.
 *
 * Billing seeders use a two-pass strategy: subscriptions are created first
 * without latest_invoice_id, invoices are created next, then InvoiceSeeder
 * links subscriptions.latest_invoice_id after both tables exist.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // 1. Reference data (no FK dependencies)
            Central\RoleAndPermissionSeeder::class,
            Central\UserSeeder::class,
            Central\PlanSeeder::class,
            Central\PlatformAnnouncementSeeder::class,
            Central\PlatformChangelogSeeder::class,
            Central\NotificationTemplateSeeder::class,

            // 2. Tenants (depends on plans)
            Central\TenantSeeder::class,

            // 3. Tenant-scoped records
            Central\DomainSeeder::class,
            Central\TenantConfigSeeder::class,
            Central\ApiKeySeeder::class,
            Central\TenantHealthCheckSeeder::class,
            Central\TenantMetricSeeder::class,
            Central\ErrorLogSeeder::class,

            // 4. Plan structure
            Central\PlanFeatureSeeder::class,

            // 5. Billing (subscriptions → invoices → link latest_invoice_id)
            Central\SubscriptionSeeder::class,
            Central\InvoiceSeeder::class,
            Central\PaymentSeeder::class,
            Central\PaymentMethodSeeder::class,

            // 6. Billing line items & usage
            Central\SubscriptionItemSeeder::class,
            Central\InvoiceItemSeeder::class,
            Central\UsageRecordSeeder::class,
            Central\SubscriptionEventSeeder::class,

            // 7. Support (depends on tenants + users)
            Central\TenantSupportTicketSeeder::class,
            Central\TenantSupportMessageSeeder::class,

            // 8. Admin tooling
            Central\TenantImpersonationTokenSeeder::class,

            // 9. Notifications & communications
            Central\NotificationSeeder::class,
            Central\SmsLogSeeder::class,
            Central\PushNotificationTokenSeeder::class,
        ]);
    }
}
