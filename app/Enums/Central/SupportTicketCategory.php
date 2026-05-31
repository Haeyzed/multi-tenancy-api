<?php

declare(strict_types=1);

namespace App\Enums\Central;

use App\Enums\Concerns\HasLabel;
use App\Enums\Concerns\InteractsWithEnum;
use App\Models\Central\TenantSupportTicket;

/**
 * Category assigned to a tenant support ticket.
 *
 * Stored on {@see TenantSupportTicket::$category} in the `tenant_support_tickets` table.
 */
enum SupportTicketCategory: string implements HasLabel
{
    use InteractsWithEnum;

    case Billing = 'billing';
    case Technical = 'technical';
    case General = 'general';
    case FeatureRequest = 'feature_request';

    /**
     * {@inheritDoc}
     */
    public function label(): string
    {
        return match ($this) {
            self::Billing => 'Billing',
            self::Technical => 'Technical',
            self::General => 'General',
            self::FeatureRequest => 'Feature Request',
        };
    }
}
