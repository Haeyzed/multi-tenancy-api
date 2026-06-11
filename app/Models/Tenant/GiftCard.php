<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Gift cards stored in the tenant database.
 * @property int $id
 * @property string|null $code
 * @property string $initial_balance
 * @property string $current_balance
 * @property string|null $currency
 * @property string|null $recipient_email
 * @property string|null $recipient_name
 * @property string|null $message
 * @property string|null $sender_id
 * @property Carbon|null $expiry_date
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|GiftCard search(?string $search)
 */
class GiftCard extends TenantModel
{
    use HasFactory;

    protected $table = 'gift_cards';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'initial_balance',
        'current_balance',
        'currency',
        'recipient_email',
        'recipient_name',
        'message',
        'sender_id',
        'expiry_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope a query by common searchable columns.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('code', 'like', "%{$search}%")
            );
        });
    }
}
