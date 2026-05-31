<?php

declare(strict_types=1);

namespace App\Models\Central;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

/**
 * Activity log entry stored in the central database.
 */
class Activity extends SpatieActivity
{
    use CentralConnection;
}
