<?php

declare(strict_types=1);

namespace Database\Seeders\Central;

use App\Enums\Central\NotificationTemplateChannel;
use App\Models\Central\NotificationTemplate;
use App\Support\Central\NotificationEmailLayout;
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
                'body_html' => NotificationEmailLayout::wrap(
                    previewText: 'Welcome to {{app_name}} — your workspace is ready',
                    greeting: 'Hello {{owner_name}},',
                    bodyContent: NotificationEmailLayout::paragraph(
                        'Your account <strong>{{tenant_name}}</strong> has been successfully created on the <strong>{{plan_name}}</strong> plan.'
                    ).NotificationEmailLayout::paragraph(
                        'Your workspace domain is <strong>{{domain}}</strong>. Sign in to your account on '
                        .'<a href="{{app_url}}" style="color:#ff641a;text-decoration:none;" target="_blank">{{app_url}}</a> '
                        .'to get started.'
                    ),
                ),
                'body_text' => "Hello {{owner_name}},\nYour account {{tenant_name}} has been created on {{plan_name}}.\nDomain: {{domain}}\nSign in: {{app_url}}",
                'variables' => ['owner_name', 'tenant_name', 'plan_name', 'domain', 'app_name', 'app_url'],
            ],
            [
                'name' => 'payment_confirmed',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Payment confirmed',
                'body_html' => NotificationEmailLayout::wrap(
                    previewText: 'Payment confirmed for {{tenant_name}}',
                    greeting: 'Hello {{owner_name}},',
                    bodyContent: NotificationEmailLayout::paragraph(
                        'We received your payment of <strong>{{amount}} {{currency}}</strong> for <strong>{{tenant_name}}</strong>.'
                    ).NotificationEmailLayout::paragraph(
                        'Invoice reference: <strong>{{invoice_number}}</strong>. Your subscription is now active.'
                    ),
                ),
                'body_text' => "Hello {{owner_name}},\nWe received your payment of {{amount}} {{currency}}.\nInvoice: {{invoice_number}}\nYour subscription is now active.",
                'variables' => ['owner_name', 'tenant_name', 'amount', 'currency', 'invoice_number', 'app_name'],
            ],
            [
                'name' => 'payment_failed',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Action required: payment failed',
                'body_html' => NotificationEmailLayout::wrap(
                    previewText: 'Payment failed for {{tenant_name}} — action required',
                    greeting: 'Hello {{owner_name}},',
                    bodyContent: NotificationEmailLayout::paragraph(
                        'We could not collect <strong>{{amount}} {{currency}}</strong> for <strong>{{tenant_name}}</strong>.'
                    ).NotificationEmailLayout::paragraph(
                        'Reason: {{reason}}. Your account has been suspended until payment is resolved.'
                    ).NotificationEmailLayout::paragraph(
                        'You can complete payment on '
                        .'<a href="{{checkout_url}}" style="color:#ff641a;text-decoration:none;" target="_blank">{{checkout_url}}</a>.'
                    ),
                ),
                'body_text' => "Hello {{owner_name}},\nWe could not collect {{amount}} {{currency}} for {{tenant_name}}.\nReason: {{reason}}\nComplete payment: {{checkout_url}}",
                'variables' => ['owner_name', 'tenant_name', 'amount', 'currency', 'reason', 'checkout_url', 'app_name'],
            ],
            [
                'name' => 'trial_ending',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Your trial ends in {{days_remaining}} days',
                'body_html' => NotificationEmailLayout::wrap(
                    previewText: 'Your {{tenant_name}} trial ends in {{days_remaining}} days',
                    greeting: 'Hello {{owner_name}},',
                    bodyContent: NotificationEmailLayout::paragraph(
                        'Your trial for <strong>{{tenant_name}}</strong> ends on <strong>{{trial_ends_at}}</strong>.'
                    ).NotificationEmailLayout::paragraph(
                        'Make sure a payment method is saved on '
                        .'<a href="{{app_url}}" style="color:#ff641a;text-decoration:none;" target="_blank">{{app_url}}</a> '
                        .'so billing continues without interruption.'
                    ),
                ),
                'body_text' => "Hello {{owner_name}},\nYour trial for {{tenant_name}} ends on {{trial_ends_at}}.\nMake sure a payment method is saved so billing continues without interruption.",
                'variables' => ['owner_name', 'tenant_name', 'days_remaining', 'trial_ends_at', 'app_url', 'app_name'],
            ],
            [
                'name' => 'payment_method_saved',
                'channel' => NotificationTemplateChannel::Email,
                'subject' => 'Payment method saved',
                'body_html' => NotificationEmailLayout::wrap(
                    previewText: 'Payment method saved for {{tenant_name}}',
                    greeting: 'Hello {{owner_name}},',
                    bodyContent: NotificationEmailLayout::paragraph(
                        'Your payment method for <strong>{{tenant_name}}</strong> has been saved successfully.'
                    ).NotificationEmailLayout::paragraph(
                        'We will use it when your trial ends or for future subscription renewals.'
                    ),
                ),
                'body_text' => "Hello {{owner_name}},\nYour payment method for {{tenant_name}} has been saved successfully.\nWe will use it when your trial ends or for future renewals.",
                'variables' => ['owner_name', 'tenant_name', 'app_name'],
            ],
            [
                'name' => 'billing_alert_admin',
                'channel' => NotificationTemplateChannel::InApp,
                'subject' => '{{event_title}}',
                'body_html' => NotificationEmailLayout::paragraph('{{event_body}}'),
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
