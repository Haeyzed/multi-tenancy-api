<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Analytics event stored in the tenant database.
 *
 * @property int $id
 * @property string $event_type
 * @property int|null $user_id
 * @property int|null $session_id
 * @property string|null $ip_address
 * @property string|null $url
 * @property string|null $referrer
 * @property string|null $user_agent
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $occurred_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AnalyticsEvent extends TenantModel
{
    use HasFactory;

    protected $table = 'analytics_events';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_type',
        'user_id',
        'session_id',
        'ip_address',
        'url',
        'referrer',
        'user_agent',
        'metadata',
        'occurred_at',
    ];

    /**
     * User associated with this event, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
