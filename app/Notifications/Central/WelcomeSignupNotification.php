<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use App\Models\Central\Tenant;
use App\Support\TenantUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSignupNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
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
        $planName = $this->tenant->plan?->name ?? 'your plan';
        $loginUrl = $this->tenant->meta['owner_login_url'] ?? TenantUrl::ownerLogin($this->tenant);
        $requiresSetup = (bool)($this->tenant->meta['owner_requires_password_setup'] ?? false);
        $setupUrl = $this->tenant->meta['owner_password_setup_url'] ?? null;

        $message = (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->greeting('Hello ' . $this->tenant->owner_name . ',')
            ->line('Your account **' . $this->tenant->name . '** has been created on ' . $planName . '.')
            ->line('Domain: ' . $this->tenant->domain);

        if ($requiresSetup && is_string($setupUrl) && $setupUrl !== '') {
            return $message
                ->line('Set your store owner password to access your workspace.')
                ->action('Set your password', $setupUrl)
                ->line('This link expires after a short period for security.');
        }

        return $message
            ->line('Your store owner account is ready.')
            ->action('Sign in to your store', $loginUrl)
            ->line('Use your work email and the password you chose during signup.');
    }
}
