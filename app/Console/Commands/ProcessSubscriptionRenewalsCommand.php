<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Central\SubscriptionBillingService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewalsCommand extends Command
{
    protected $signature = 'subscriptions:process-renewals';

    protected $description = 'Bill subscriptions whose billing period has ended';

    public function handle(SubscriptionBillingService $billing): int
    {
        $count = $billing->processDueRenewals();

        $this->info("Processed {$count} subscription renewal(s).");

        return self::SUCCESS;
    }
}
