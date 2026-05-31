<?php

declare(strict_types=1);

namespace App\Events\Central;

use App\Models\Central\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrialEndingSoon
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining,
    ) {}
}
