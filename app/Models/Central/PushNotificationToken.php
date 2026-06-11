<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\PushDeviceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Push notification device token for a platform user.
 *
 * @property int $id
 * @property int $user_id
 * @property PushDeviceType $device_type
 * @property string $device_token
 * @property bool $is_active
 * @property Carbon|null $last_used_at
 */
class PushNotificationToken extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'device_type',
        'device_token',
        'is_active',
        'last_used_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'device_type' => PushDeviceType::class,
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }
}
