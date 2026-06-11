<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Models\Central\TenantSupportMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central TenantSupportMessage records and queries.
 */
class TenantSupportMessageService
{
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'ticket.tenant',
        'sender',
    ];

    /**
     * Get all TenantSupportMessage records.
     *
     * @return Collection<int, TenantSupportMessage>
     */
    public function getAll(): Collection
    {
        return TenantSupportMessage::query()
            ->with(self::LIST_RELATIONS)
            ->latest()
            ->get();
    }

    /**
     * @param list<string> $isRead
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null,
        ?int    $ticketId = null,
        array   $isRead = [],
    ): LengthAwarePaginator
    {
        return TenantSupportMessage::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterTicket($ticketId)
            ->filterIsRead($isRead)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find TenantSupportMessage by ID.
     *
     * @param int $id Record identifier.
     */
    public function find(int $id): ?TenantSupportMessage
    {
        return TenantSupportMessage::query()->find($id);
    }

    /**
     * Find TenantSupportMessage by ID or fail.
     *
     * @param int $id Record identifier.
     */
    public function findOrFail(int $id): TenantSupportMessage
    {
        return TenantSupportMessage::query()
            ->with(self::LIST_RELATIONS)
            ->findOrFail($id);
    }

    /**
     * Create a new TenantSupportMessage.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): TenantSupportMessage
    {
        $message = TenantSupportMessage::query()->create($data);

        return $message->load(self::LIST_RELATIONS);
    }

    /**
     * Delete TenantSupportMessage.
     *
     * @param TenantSupportMessage $tenantSupportMessage The model instance to delete.
     */
    public function delete(TenantSupportMessage $tenantSupportMessage): bool
    {
        return (bool)$tenantSupportMessage->delete();
    }

    /**
     * Mark a support message as read.
     *
     * @param TenantSupportMessage $supportMessage The message to mark as read.
     */
    public function markAsRead(TenantSupportMessage $supportMessage): TenantSupportMessage
    {
        $supportMessage->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $supportMessage->fresh(self::LIST_RELATIONS);
    }

    /**
     * Update TenantSupportMessage.
     *
     * @param TenantSupportMessage $tenantSupportMessage The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(TenantSupportMessage $tenantSupportMessage, array $data): TenantSupportMessage
    {
        $tenantSupportMessage->update($data);

        return $tenantSupportMessage->fresh(self::LIST_RELATIONS);
    }

    /**
     * Get messages by ticket with sender eager loaded.
     *
     * @param int $ticketId Support ticket ID.
     * @return Collection<int, TenantSupportMessage>
     */
    public function getByTicketWithSender(int $ticketId): Collection
    {
        return TenantSupportMessage::query()->with('sender')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
