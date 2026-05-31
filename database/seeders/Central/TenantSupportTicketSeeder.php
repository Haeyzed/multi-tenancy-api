<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\SupportTicketCategory;
use App\Enums\Central\SupportTicketPriority;
use App\Enums\Central\SupportTicketStatus;
use App\Models\Central\TenantSupportTicket;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed support tickets for demo tenants.
 */
class TenantSupportTicketSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TenantSupportTicket::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('acme-corp')->id,
                'subject' => 'API returning 500 errors',
            ],
            [
                'category' => SupportTicketCategory::Technical,
                'priority' => SupportTicketPriority::High,
                'status' => SupportTicketStatus::InProgress,
                'body' => 'Our API integration started failing this morning with 500 Internal Server Error. Please investigate urgently.',
                'assigned_to' => $this->userId('david.tech@platform.com'),
                'resolved_at' => null,
            ],
        );

        TenantSupportTicket::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('beta-solutions')->id,
                'subject' => 'Request invoice for tax purposes',
            ],
            [
                'category' => SupportTicketCategory::Billing,
                'priority' => SupportTicketPriority::Medium,
                'status' => SupportTicketStatus::Open,
                'body' => 'I need a formal invoice with our company VAT number for the last 3 months.',
                'assigned_to' => $this->userId('carol.billing@platform.com'),
                'resolved_at' => null,
            ],
        );

        TenantSupportTicket::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('gamma-innovations')->id,
                'subject' => 'How to connect custom domain?',
            ],
            [
                'category' => SupportTicketCategory::General,
                'priority' => SupportTicketPriority::Low,
                'status' => SupportTicketStatus::WaitingCustomer,
                'body' => 'I would like to use my own domain instead of the default subdomain. What are the steps?',
                'assigned_to' => $this->userId('alice.support@platform.com'),
                'resolved_at' => null,
            ],
        );

        TenantSupportTicket::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('delta-works')->id,
                'subject' => 'Dark mode for dashboard',
            ],
            [
                'category' => SupportTicketCategory::FeatureRequest,
                'priority' => SupportTicketPriority::Low,
                'status' => SupportTicketStatus::Closed,
                'body' => 'Would love to see a dark mode option in the admin dashboard.',
                'assigned_to' => null,
                'resolved_at' => now()->subDays(5),
            ],
        );

        TenantSupportTicket::query()->updateOrCreate(
            [
                'tenant_id' => $this->tenant('acme-corp')->id,
                'subject' => 'Double charged this month',
            ],
            [
                'category' => SupportTicketCategory::Billing,
                'priority' => SupportTicketPriority::Urgent,
                'status' => SupportTicketStatus::Resolved,
                'body' => 'I noticed two charges on my credit card for the same billing period. Please refund the duplicate.',
                'assigned_to' => $this->userId('carol.billing@platform.com'),
                'resolved_at' => now()->subDays(2),
            ],
        );
    }
}
