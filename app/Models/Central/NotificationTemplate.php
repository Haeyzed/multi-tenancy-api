<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\NotificationTemplateChannel;
use Illuminate\Database\Eloquent\Model;

/**
 * Reusable notification content template for outbound communications.
 *
 * @property int $id
 * @property string $name
 * @property NotificationTemplateChannel $channel
 * @property string|null $subject
 * @property string|null $body_html
 * @property string|null $body_text
 * @property array<string, mixed>|null $variables
 * @property bool $is_active
 */
class NotificationTemplate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'channel',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => NotificationTemplateChannel::class,
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
