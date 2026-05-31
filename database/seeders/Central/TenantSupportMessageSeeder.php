<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\MessageSenderType;
use App\Models\Central\TenantSupportMessage;
use App\Models\Central\TenantSupportTicket;
use Database\Seeders\Central\Concerns\InteractsWithCentralSeeders;
use Illuminate\Database\Seeder;

/**
 * Seed support ticket message threads.
 */
class TenantSupportMessageSeeder extends Seeder
{
    use InteractsWithCentralSeeders;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apiTicket = TenantSupportTicket::query()
            ->where('subject', 'API returning 500 errors')
            ->firstOrFail();

        TenantSupportMessage::query()->updateOrCreate(
            [
                'ticket_id' => $apiTicket->id,
                'body' => 'Our API integration started failing this morning with 500 Internal Server Error. Please investigate urgently.',
            ],
            [
                'sender_type' => MessageSenderType::User,
                'sender_id' => null,
                'is_internal' => false,
                'is_read' => true,
                'read_at' => now()->subDays(1),
            ],
        );

        TenantSupportMessage::query()->updateOrCreate(
            [
                'ticket_id' => $apiTicket->id,
                'body' => 'Thanks for reporting. I am looking into this now. Can you share the exact endpoint and timestamp?',
            ],
            [
                'sender_type' => MessageSenderType::Admin,
                'sender_id' => $this->userId('david.tech@platform.com'),
                'is_internal' => false,
                'is_read' => true,
                'read_at' => now()->subDays(1)->addMinutes(30),
            ],
        );

        TenantSupportMessage::query()->updateOrCreate(
            [
                'ticket_id' => $apiTicket->id,
                'body' => 'Status changed to: In Progress',
            ],
            [
                'sender_type' => MessageSenderType::System,
                'sender_id' => null,
                'is_internal' => true,
                'is_read' => true,
                'read_at' => now()->subDays(1)->addMinutes(35),
            ],
        );

        $invoiceTicket = TenantSupportTicket::query()
            ->where('subject', 'Request invoice for tax purposes')
            ->firstOrFail();

        TenantSupportMessage::query()->updateOrCreate(
            [
                'ticket_id' => $invoiceTicket->id,
                'body' => 'I need a formal invoice with our company VAT number for the last 3 months.',
            ],
            [
                'sender_type' => MessageSenderType::User,
                'sender_id' => null,
                'is_internal' => false,
                'is_read' => false,
                'read_at' => null,
            ],
        );

        $domainTicket = TenantSupportTicket::query()
            ->where('subject', 'How to connect custom domain?')
            ->firstOrFail();

        TenantSupportMessage::query()->updateOrCreate(
            [
                'ticket_id' => $domainTicket->id,
                'body' => 'I would like to use my own domain instead of the default subdomain. What are the steps?',
            ],
            [
                'sender_type' => MessageSenderType::User,
                'sender_id' => null,
                'is_internal' => false,
                'is_read' => true,
                'read_at' => now()->subDays(3),
            ],
        );

        TenantSupportMessage::query()->updateOrCreate(
            [
                'ticket_id' => $domainTicket->id,
                'body' => 'You can add a custom domain in Settings > Domains. You will need to update your DNS records as shown in the instructions.',
            ],
            [
                'sender_type' => MessageSenderType::Admin,
                'sender_id' => $this->userId('alice.support@platform.com'),
                'is_internal' => false,
                'is_read' => false,
                'read_at' => null,
            ],
        );

        TenantSupportMessage::query()->updateOrCreate(
            [
                'ticket_id' => $domainTicket->id,
                'body' => 'Status changed to: Waiting for Customer',
            ],
            [
                'sender_type' => MessageSenderType::System,
                'sender_id' => null,
                'is_internal' => true,
                'is_read' => true,
                'read_at' => now()->subDays(2),
            ],
        );
    }
}
