<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\NotificationTemplateChannel;
use App\Models\Central\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * Seed reusable notification templates for billing and signup flows.
 */
class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'welcome_signup',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Welcome to {{app_name}}',
                'body_html' => '<p>Hello {{owner_name}},</p><p>Your account <strong>{{tenant_name}}</strong> has been created on {{plan_name}}.</p><p>Domain: {{domain}}</p><p>You can now sign in and start using your workspace.</p>',
                'body_text' => "Hello {{owner_name}},\nYour account {{tenant_name}} has been created on {{plan_name}}.\nDomain: {{domain}}\nYou can now sign in and start using your workspace.",
                'variables' => ['owner_name', 'tenant_name', 'plan_name', 'domain', 'app_name'],
            ],
            [
                'name' => 'payment_confirmed',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Payment confirmed',
                'body_html' => '<p>Hello {{owner_name}},</p><p>We received your payment of <strong>{{amount}} {{currency}}</strong>.</p><p>Invoice: {{invoice_number}}</p><p>Your subscription is now active.</p>',
                'body_text' => "Hello {{owner_name}},\nWe received your payment of {{amount}} {{currency}}.\nInvoice: {{invoice_number}}\nYour subscription is now active.",
                'variables' => ['owner_name', 'tenant_name', 'amount', 'currency', 'invoice_number'],
            ],
            [
                'name' => 'payment_failed',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Action required: payment failed',
                'body_html' => '<p>Hello {{owner_name}},</p><p>We could not collect <strong>{{amount}} {{currency}}</strong> for <strong>{{tenant_name}}</strong>.</p><p>Reason: {{reason}}</p><p>Your account has been suspended until payment is resolved.</p>',
                'body_text' => "Hello {{owner_name}},\nWe could not collect {{amount}} {{currency}} for {{tenant_name}}.\nReason: {{reason}}\nYour account has been suspended until payment is resolved.",
                'variables' => ['owner_name', 'tenant_name', 'amount', 'currency', 'reason', 'checkout_url'],
            ],
            [
                'name' => 'trial_ending',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Your trial ends in {{days_remaining}} days',
                'body_html' => '<p>Hello {{owner_name}},</p><p>Your trial for <strong>{{tenant_name}}</strong> ends on {{trial_ends_at}}.</p><p>Make sure a payment method is saved so billing continues without interruption.</p>',
                'body_text' => "Hello {{owner_name}},\nYour trial for {{tenant_name}} ends on {{trial_ends_at}}.\nMake sure a payment method is saved so billing continues without interruption.",
                'variables' => ['owner_name', 'tenant_name', 'days_remaining', 'trial_ends_at'],
            ],
            [
                'name' => 'payment_method_saved',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Payment method saved',
                'body_html' => '<p>Hello {{owner_name}},</p><p>Your payment method for <strong>{{tenant_name}}</strong> has been saved successfully.</p><p>We will use it when your trial ends or for future renewals.</p>',
                'body_text' => "Hello {{owner_name}},\nYour payment method for {{tenant_name}} has been saved successfully.\nWe will use it when your trial ends or for future renewals.",
                'variables' => ['owner_name', 'tenant_name'],
            ],
            [
                'name' => 'billing_alert_admin',
                'channel' => NotificationTemplateChannel::InApp,
                'subject' => '{{event_title}}',
                'body_html' => '<p>{{event_body}}</p>',
                'body_text' => '{{event_body}}',
                'variables' => ['event_title', 'event_body', 'tenant_name', 'tenant_id'],
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::query()->updateOrCreate(
                ['name' => $template['name']],
                $template,
            );
        }
    }
}
