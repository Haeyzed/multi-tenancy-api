<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Customer loyalty accounts stored in the tenant database.
 * @property string $id
 * @property string $user_id
 * @property string $program_id
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
    use HasFactory, HasUuids;

    protected $table = 'customer_loyalty_accounts';

    public $incrementing = false;

    protected $keyType = 'string';

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

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
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
