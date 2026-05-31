<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Central\SubscriptionBillingService;
use Illuminate\Console\Command;

class ProcessTrialExpirationsCommand extends Command
{
    protected $signature = 'subscriptions:process-trial-expirations';

    protected $description = 'Bill subscriptions whose trial period has ended';

    public function handle(SubscriptionBillingService $billing): int
    {
        $count = $billing->processExpiredTrials();

        $this->info("Processed {$count} trial expiration(s).");

        return self::SUCCESS;
    }
}
