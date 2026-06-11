<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Chat messages stored in the tenant database.
 * @property int $id
 * @property string $session_id
 * @property string $sender_type
 * @property string|null $sender_id
 * @property string|null $message
 * @property int|null $attachment_media_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ChatMessage extends TenantModel
{
    use HasFactory;

    protected $table = 'chat_messages';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'session_id',
        'sender_type',
        'sender_id',
        'message',
        'attachment_media_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
