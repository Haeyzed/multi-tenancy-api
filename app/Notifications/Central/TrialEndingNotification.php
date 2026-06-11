<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant       $tenant,
        private readonly Subscription $subscription,
        private readonly int          $daysRemaining,
    )
    {
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $endsAt = $this->subscription->trial_ends_at?->toFormattedDateString() ?? 'soon';

        return (new MailMessage)
            ->subject('Your trial ends in ' . $this->daysRemaining . ' days')
            ->greeting('Hello ' . $this->tenant->owner_name . ',')
            ->line('Your trial for **' . $this->tenant->name . '** ends on ' . $endsAt . '.')
            ->line('Make sure a payment method is saved so billing continues without interruption.');
    }
}
