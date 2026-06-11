<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Gift card stored in the tenant database.
 *
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
 * @method static Builder|GiftCard filterIsActive(array $statuses)
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

    /**
     * Scope a query to search by code.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, function (Builder $q, string $search) {
            $q->where(function (Builder $q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Filter by active/inactive status tokens (active, inactive).
     *
     * @param list<string> $statuses
     */
    public function scopeFilterIsActive(Builder $query, array $statuses): void
    {
        $values = QueryFilter::booleanStatuses($statuses);

        $query->when($values !== [], fn(Builder $q) => $q->whereIn('is_active', $values));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
}
