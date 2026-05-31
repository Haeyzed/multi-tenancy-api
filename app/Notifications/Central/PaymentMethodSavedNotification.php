<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentMethodSavedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
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
        return (new MailMessage)
            ->subject('Payment method saved')
            ->greeting('Hello '.$this->tenant->owner_name.',')
            ->line('Your payment method for **'.$this->tenant->name.'** has been saved successfully.')
            ->line('We will use it when your trial ends or for future renewals.');
    }
}
