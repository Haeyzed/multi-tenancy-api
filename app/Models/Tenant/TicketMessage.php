<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Ticket messages stored in the tenant database.
 *
 * @property int $id
 * @property string $ticket_id
 * @property string $sender_type
 * @property string|null $sender_id
 * @property string|null $body
 * @property bool $is_internal
 * @property bool $is_read
 * @property Carbon|null $read_at
 * @property array<string, mixed>|null $attachment_media_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TicketMessage extends TenantModel
{
    use HasFactory;

    protected $table = 'ticket_messages';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'body',
        'is_internal',
        'is_read',
        'read_at',
        'attachment_media_ids',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'attachment_media_ids' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
