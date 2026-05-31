<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use App\Models\Central\Invoice;
use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly Invoice $invoice,
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
        $amount = number_format($this->invoice->amount_due / 100, 2);

        return (new MailMessage)
            ->subject('Payment confirmed')
            ->greeting('Hello '.$this->tenant->owner_name.',')
            ->line('We received your payment of **'.$amount.' '.$this->invoice->currency.'**.')
            ->line('Invoice: '.$this->invoice->invoice_number)
            ->line('Your subscription is now active.');
    }
}
