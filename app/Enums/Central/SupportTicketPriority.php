<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\TenantSupportTicket;

/**
 * Priority level assigned to a tenant support ticket.
 *
 * Stored on {@see TenantSupportTicket::$priority} in the `tenant_support_tickets` table.
 */
enum SupportTicketPriority: string implements HasLabel
{
    use InteractsWithEnum;

    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }
}
