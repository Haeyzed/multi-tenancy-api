<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use App\Enums\Central\OtpPurpose;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuthOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string     $otp,
        private readonly OtpPurpose $purpose,
        private readonly int        $expiresInMinutes,
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
        return (new MailMessage)
            ->subject($this->subject())
            ->greeting('Hello,')
            ->line($this->introLine())
            ->line('Your verification code is: **' . $this->otp . '**')
            ->line('This code expires in ' . $this->expiresInMinutes . ' minutes.')
            ->line('If you did not request this code, you can safely ignore this email.');
    }

    private function subject(): string
    {
        return match ($this->purpose) {
            OtpPurpose::PasswordReset => 'Reset your password',
            OtpPurpose::PasswordChange => 'Confirm your password change',
            OtpPurpose::EmailVerification => 'Verify your email address',
        };
    }

    private function introLine(): string
    {
        return match ($this->purpose) {
            OtpPurpose::PasswordReset => 'Use the code below to reset your password.',
            OtpPurpose::PasswordChange => 'Use the code below to confirm your password change.',
            OtpPurpose::EmailVerification => 'Use the code below to verify your email address.',
        };
    }
}
