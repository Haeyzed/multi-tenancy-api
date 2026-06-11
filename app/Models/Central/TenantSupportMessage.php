<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\MessageSenderType;
use App\Support\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Reply or note posted on a tenant support ticket.
 *
 * @property int $id
 * @property int $ticket_id
 * @property MessageSenderType $sender_type
 * @property int|null $sender_id
 * @property string $body
 * @property bool $is_internal
 * @property bool $is_read
 * @property Carbon|null $read_at
 */
class TenantSupportMessage extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'body',
        'is_internal',
        'is_read',
        'read_at',
    ];

    /**
     * Scope messages to tickets belonging to the selected tenant.
     */
    public function scopeForTenant(Builder $query, ?string $tenantId = null): void
    {
        $tenantId ??= request()->header('X-Tenant-Id');

        $query->when($tenantId, function (Builder $q, string $tenantId) {
            $q->whereHas('ticket', fn(Builder $q) => $q->where('tenant_id', $tenantId));
        });
    }

    /**
     * Scope a query to search by message body.
     */
    public function scopeSearch(Builder $query, ?string $search): void
    {
        $query->when($search, fn(Builder $q, string $search) => $q->where('body', 'like', "%{$search}%"));
    }

    /**
     * Filter by parent ticket ID.
     */
    public function scopeFilterTicket(Builder $query, ?int $ticketId): void
    {
        $query->when($ticketId, fn(Builder $q) => $q->where('ticket_id', $ticketId));
    }

    /**
     * Filter by read/unread tokens.
     *
     * @param list<string> $values
     */
    public function scopeFilterIsRead(Builder $query, array $values): void
    {
        $mapped = QueryFilter::booleanRead($values);

        if ($mapped === []) {
            return;
        }

        $query->where(function (Builder $q) use ($mapped) {
            foreach ($mapped as $isRead) {
                $q->orWhere('is_read', $isRead);
            }
        });
    }

    /**
     * Support ticket this message belongs to.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TenantSupportTicket::class, 'ticket_id');
    }

    /**
     * Platform user who authored this message, when applicable.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sender_type' => MessageSenderType::class,
            'is_internal' => 'boolean',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }
}
