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
     * Get all TenantSupportMessage records.
     *
     * @return Collection<int, TenantSupportMessage>
     */
    public function getAll(): Collection
    {
        return TenantSupportMessage::query()->get();
    }

    /**
     * Get paginated TenantSupportMessage records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, TenantSupportMessage>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return TenantSupportMessage::query()->paginate($perPage);
    }

    /**
     * Find TenantSupportMessage by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?TenantSupportMessage
    {
        return TenantSupportMessage::query()->find($id);
    }

    /**
     * Find TenantSupportMessage by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): TenantSupportMessage
    {
        return TenantSupportMessage::query()->findOrFail($id);
    }

    /**
     * Create a new TenantSupportMessage.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TenantSupportMessage
    {
        return TenantSupportMessage::query()->create($data);
    }

    /**
     * Update TenantSupportMessage.
     *
     * @param  TenantSupportMessage  $tenantSupportMessage  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(TenantSupportMessage $tenantSupportMessage, array $data): TenantSupportMessage
    {
        $tenantSupportMessage->query()->update($data);

        return $tenantSupportMessage->fresh();
    }

    /**
     * Delete TenantSupportMessage.
     *
     * @param  TenantSupportMessage  $tenantSupportMessage  The model instance to delete.
     */
    public function delete(TenantSupportMessage $tenantSupportMessage): bool
    {
        return $tenantSupportMessage->query()->delete() > 0;
    }

    /**
     * Mark a support message as read.
     *
     * @param  TenantSupportMessage  $supportMessage  The message to mark as read.
     */
    public function markAsRead(TenantSupportMessage $supportMessage): TenantSupportMessage
    {
        $supportMessage->query()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $supportMessage->fresh();
    }

    /**
     * Get messages by ticket with sender eager loaded.
     *
     * @param  int  $ticketId  Support ticket ID.
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
