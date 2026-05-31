<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Mail notification rendered from a central notification template.
 */
class TemplatedMailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $mailSubject,
        private readonly ?string $bodyHtml,
        private readonly ?string $bodyText,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->subject($this->mailSubject);

        if ($this->bodyText !== null && $this->bodyText !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $this->bodyText) ?: [] as $line) {
                if ($line !== '') {
                    $message->line($line);
                }
            }
        } elseif ($this->bodyHtml !== null && $this->bodyHtml !== '') {
            $message->line(strip_tags($this->bodyHtml));
        }

        return $message;
    }
}
