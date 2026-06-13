<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\UserNotificationChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * In-app (or multi-channel) notification stored for a platform user.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $body
 * @property array<string, mixed>|null $data
 * @property UserNotificationChannel $channel
 * @property bool $is_read
 * @property Carbon|null $read_at
 * @property Carbon|null $sent_at
 */
class UserNotification extends Model
{
    protected $table = 'notifications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'channel',
        'is_read',
        'read_at',
        'sent_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if ($this->is_read) {
            return;
        }

        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'channel' => UserNotificationChannel::class,
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }
}
