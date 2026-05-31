<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\TenantSupportTicket;

/**
 * Workflow status of a tenant support ticket.
 *
 * Stored on {@see TenantSupportTicket::$status} in the `tenant_support_tickets` table.
 */
enum SupportTicketStatus: string implements HasLabel
{
    use InteractsWithEnum;

    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingCustomer = 'waiting_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::WaitingCustomer => 'Waiting Customer',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}
