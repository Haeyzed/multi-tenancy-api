<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * User sessions stored in the tenant database.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array<string, mixed>|null $device_info
 * @property array<string, mixed>|null $location
 * @property Carbon|null $last_activity_at
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UserSession extends TenantModel
{
    use HasFactory;

    protected $table = 'user_sessions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_info',
        'location',
        'last_activity_at',
        'expires_at',
    ];

    /**
     * Related User.
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
            'device_info' => 'array',
            'location' => 'array',
            'last_activity_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
