<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Live chat sessions stored in the tenant database.
 * @property string $id
 * @property string|null $user_id
 * @property string|null $guest_email
 * @property string|null $guest_name
 * @property string|null $assigned_to
 * @property string $status
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 * @property int|null $satisfaction_rating
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LiveChatSession extends TenantModel
{
    use HasFactory, HasUuids;

    protected $table = 'live_chat_sessions';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'guest_email',
        'guest_name',
        'assigned_to',
        'status',
        'started_at',
        'ended_at',
        'satisfaction_rating',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Related User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
