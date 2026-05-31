<?php

declare(strict_types=1);

namespace App\Enums\Central;

enum PushDeviceType: string
{
    case Ios = 'ios';
    case Android = 'android';
    case Web = 'web';
}
