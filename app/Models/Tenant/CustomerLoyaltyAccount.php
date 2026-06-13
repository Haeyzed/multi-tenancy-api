<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Customer loyalty account stored in the tenant database.
 *
 * @property int $id
 * @property int $user_id
 * @property int $program_id
 * @property int|null $tier_id
 * @property int $total_points
 * @property int $available_points
 * @property int $lifetime_points
 * @property Carbon|null $last_activity_at
 * @property string|null $card_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class CustomerLoyaltyAccount extends TenantModel
{
    use HasFactory;

    protected $table = 'customer_loyalty_accounts';
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'program_id',
        'tier_id',
        'total_points',
        'available_points',
        'lifetime_points',
        'last_activity_at',
        'card_number',
    ];

    /**
     * Customer who owns this loyalty account.
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
            'last_activity_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
