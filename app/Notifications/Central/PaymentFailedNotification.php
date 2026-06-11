<?php

declare(strict_types=1);

namespace App\Notifications\Central;

use App\Models\Central\Invoice;
use App\Models\Central\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant  $tenant,
        private readonly Invoice $invoice,
        private readonly string  $reason,
        private readonly ?string $checkoutUrl = null,
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
        $amount = number_format($this->invoice->amount_due / 100, 2);

        $message = (new MailMessage)
            ->subject('Action required: payment failed')
            ->greeting('Hello ' . $this->tenant->owner_name . ',')
            ->line('We could not collect **' . $amount . ' ' . $this->invoice->currency . '** for **' . $this->tenant->name . '**.')
            ->line('Reason: ' . $this->reason)
            ->line('Your account has been suspended until payment is resolved.');

        if ($this->checkoutUrl !== null) {
            $message->action('Complete payment', $this->checkoutUrl);
        }

        return $message;
    }
}
