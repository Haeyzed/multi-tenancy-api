<?php

declare(strict_types=1);

namespace App\Enums\Central;

enum SmsLogStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Queued = 'queued';
}
