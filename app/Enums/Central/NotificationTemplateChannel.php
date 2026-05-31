<?php

declare(strict_types=1);

namespace App\Enums\Central;

enum NotificationTemplateChannel: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Push = 'push';
    case InApp = 'in_app';
    case Whatsapp = 'whatsapp';
    case Slack = 'slack';
}
