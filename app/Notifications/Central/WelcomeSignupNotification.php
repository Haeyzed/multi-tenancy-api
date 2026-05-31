<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSignupNotification extends Notification implements ShouldQueue
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
        $planName = $this->tenant->plan?->name ?? 'your plan';

        return (new MailMessage)
            ->subject('Welcome to '.config('app.name'))
            ->greeting('Hello '.$this->tenant->owner_name.',')
            ->line('Your account **'.$this->tenant->name.'** has been created on '.$planName.'.')
            ->line('Domain: '.$this->tenant->domain)
            ->line('You can now sign in and start using your workspace.');
    }
}
