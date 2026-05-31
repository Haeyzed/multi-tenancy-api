<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\MessageSenderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Reply or note posted on a tenant support ticket.
 *
 * @property int $id
 * @property int $ticket_id
 * @property MessageSenderType $sender_type
 * @property int|null $sender_id
 * @property string $body
 * @property bool $is_internal
 * @property bool $is_read
 * @property Carbon|null $read_at
 */
class TenantSupportMessage extends Model
{
    use HasFactory;

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
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sender_type' => MessageSenderType::class,
            'is_internal' => 'boolean',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * Support ticket this message belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TenantSupportTicket::class, 'ticket_id');
    }

    /**
     * Platform user who authored this message, when applicable.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
