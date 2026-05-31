<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Central\SubscriptionBillingService;
use Illuminate\Console\Command;

class SendTrialEndingRemindersCommand extends Command
{
    protected $signature = 'subscriptions:send-trial-reminders';

    protected $description = 'Send email reminders for trials ending soon';

    public function handle(SubscriptionBillingService $billing): int
    {
        $count = $billing->sendTrialEndingReminders();

        $this->info("Sent {$count} trial reminder(s).");

        return self::SUCCESS;
    }
}
