<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\SmsLogStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Outbound SMS delivery log entry.
 *
 * @property int $id
 * @property string $recipient
 * @property string $message
 * @property SmsLogStatus $status
 * @property string $provider
 * @property string|null $provider_message_id
 * @property string|null $cost
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property string|null $error_message
 */
class SmsLog extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'recipient',
        'message',
        'status',
        'provider',
        'provider_message_id',
        'cost',
        'sent_at',
        'delivered_at',
        'error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SmsLogStatus::class,
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
