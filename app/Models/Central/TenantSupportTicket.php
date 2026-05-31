<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Enums\Central\SupportTicketCategory;
use App\Enums\Central\SupportTicketPriority;
use App\Enums\Central\SupportTicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Cross-tenant support ticket managed by platform staff.
 *
 * @property int $id
 * @property string $tenant_id
 * @property SupportTicketCategory $category
 * @property SupportTicketPriority $priority
 * @property SupportTicketStatus $status
 * @property string $subject
 * @property string $body
 * @property int|null $assigned_to
 * @property Carbon|null $resolved_at
 */
class TenantSupportTicket extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'category',
        'priority',
        'status',
        'subject',
        'body',
        'assigned_to',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => SupportTicketCategory::class,
            'priority' => SupportTicketPriority::class,
            'status' => SupportTicketStatus::class,
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Tenant that opened this support ticket.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Platform administrator assigned to this ticket.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Messages posted on this support ticket.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(TenantSupportMessage::class, 'ticket_id');
    }
}
