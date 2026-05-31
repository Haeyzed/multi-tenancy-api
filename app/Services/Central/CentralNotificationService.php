<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\NotificationTemplateChannel;
use App\Enums\Central\SmsLogStatus;
use App\Enums\Central\UserNotificationChannel;
use App\Models\Central\NotificationTemplate;
use App\Models\Central\SmsLog;
use App\Models\Central\User;
use App\Models\Central\UserNotification;
use App\Notifications\Central\TemplatedMailNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

/**
 * Dispatch notifications using stored templates across email, in-app, and SMS logs.
 */
class CentralNotificationService
{
    public function __construct(
        private readonly NotificationTemplateRenderer $renderer,
    ) {}

    /**
     * Send a templated notification to external recipients and optional platform users.
     *
     * @param  array<string, scalar|null>  $variables
     * @param  list<string>  $mailRecipients
     * @param  list<User>  $inAppUsers
     * @param  list<string>  $smsRecipients
     * @return array{
     *     template: string,
     *     emails_sent: int,
     *     in_app_created: int,
     *     sms_logged: int
     * }
     */
    public function sendFromTemplate(
        string $templateName,
        array $variables = [],
        array $mailRecipients = [],
        array $inAppUsers = [],
        array $smsRecipients = [],
        ?string $notificationType = null,
    ): array {
        $template = NotificationTemplate::query()
            ->where('name', $templateName)
            ->where('is_active', true)
            ->first();

        if ($template === null) {
            throw new RuntimeException("Notification template [{$templateName}] is not configured.");
        }

        $subject = $this->renderer->render($template->subject, $variables) ?? config('app.name');
        $bodyHtml = $this->renderer->render($template->body_html, $variables);
        $bodyText = $this->renderer->render($template->body_text, $variables);
        $body = $bodyText ?? strip_tags((string) $bodyHtml) ?? $subject;

        $emailsSent = 0;
        $inAppCreated = 0;
        $smsLogged = 0;

        if ($template->channel === NotificationTemplateChannel::Email && $mailRecipients !== []) {
            foreach (array_unique($mailRecipients) as $email) {
                Notification::route('mail', $email)
                    ->notify(new TemplatedMailNotification($subject, $bodyHtml, $bodyText));

                $emailsSent++;
            }
        }

        foreach ($inAppUsers as $user) {
            UserNotification::query()->create([
                'user_id' => $user->id,
                'type' => $notificationType ?? $templateName,
                'title' => $subject,
                'body' => $body,
                'data' => $variables,
                'channel' => UserNotificationChannel::InApp,
                'sent_at' => now(),
            ]);

            $inAppCreated++;
        }

        if ($smsRecipients !== []) {
            foreach (array_unique($smsRecipients) as $recipient) {
                SmsLog::query()->create([
                    'recipient' => $recipient,
                    'message' => $body,
                    'status' => SmsLogStatus::Queued,
                    'provider' => 'twilio',
                    'sent_at' => now(),
                ]);

                $smsLogged++;
            }
        }

        return [
            'template' => $templateName,
            'emails_sent' => $emailsSent,
            'in_app_created' => $inAppCreated,
            'sms_logged' => $smsLogged,
        ];
    }

    /**
     * Platform users who should receive billing alerts in-app.
     *
     * @return Collection<int, User>
     */
    public function billingAlertRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['billing', 'super_admin']))
            ->get();
    }
}
