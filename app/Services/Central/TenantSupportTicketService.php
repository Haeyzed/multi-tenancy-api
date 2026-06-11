<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\SupportTicketPriority;
use App\Enums\Central\SupportTicketStatus;
use App\Models\Central\TenantSupportTicket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central TenantSupportTicket records and queries.
 */
class TenantSupportTicketService
{
    /**
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
        'assignee',
    ];

    /**
     * Relations eager loaded for detail responses with conversation.
     *
     * @var list<string>
     */
    private const DETAIL_RELATIONS = [
        'tenant',
        'assignee',
        'messages.sender',
    ];

    /**
     * Get all TenantSupportTicket records.
     *
     * @param string|null $search Optional search term.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getAll(?string $search = null): Collection
    {
        return TenantSupportTicket::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * @param list<string> $status
     * @param list<string> $priority
     * @param list<string> $category
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null,
        array   $status = [],
        array   $priority = [],
        array   $category = [],
    ): LengthAwarePaginator
    {
        return TenantSupportTicket::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterStatus($status)
            ->filterPriority($priority)
            ->filterCategory($category)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find TenantSupportTicket by ID.
     *
     * @param int $id Record identifier.
     */
    public function find(int $id): ?TenantSupportTicket
    {
        return TenantSupportTicket::query()->find($id);
    }

    /**
     * Find TenantSupportTicket by ID or fail.
     *
     * @param int $id Record identifier.
     */
    public function findOrFail(int $id): TenantSupportTicket
    {
        return TenantSupportTicket::query()
            ->with(self::DETAIL_RELATIONS)
            ->findOrFail($id);
    }

    /**
     * Create a new TenantSupportTicket.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): TenantSupportTicket
    {
        $data['status'] ??= SupportTicketStatus::Open->value;

        $ticket = TenantSupportTicket::query()->create($data);

        return $ticket->load(self::LIST_RELATIONS);
    }

    /**
     * Delete TenantSupportTicket.
     *
     * @param TenantSupportTicket $tenantSupportTicket The model instance to delete.
     */
    public function delete(TenantSupportTicket $tenantSupportTicket): bool
    {
        return (bool)$tenantSupportTicket->delete();
    }

    /**
     * Filter by tenant.
     *
     * @param string $tenantId Tenant UUID.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return TenantSupportTicket::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param string $status Status value to filter by.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByStatus(string $status): Collection
    {
        return TenantSupportTicket::query()->where('status', $status)->get();
    }

    /**
     * Filter by category.
     *
     * @param string $category Ticket category to filter by.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByCategory(string $category): Collection
    {
        return TenantSupportTicket::query()->where('category', $category)->get();
    }

    /**
     * Filter by priority.
     *
     * @param string $priority Ticket priority to filter by.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByPriority(string $priority): Collection
    {
        return TenantSupportTicket::query()->where('priority', $priority)->get();
    }

    /**
     * Assign a support ticket to a platform administrator.
     *
     * @param TenantSupportTicket $supportTicket The ticket to assign.
     * @param int $adminId ID of the administrator assignee.
     */
    public function assign(TenantSupportTicket $supportTicket, int $adminId): TenantSupportTicket
    {
        $supportTicket->update([
            'assigned_to' => $adminId,
            'status' => $supportTicket->status === SupportTicketStatus::Open
                ? SupportTicketStatus::InProgress->value
                : $supportTicket->status->value,
        ]);

        return $supportTicket->fresh(self::LIST_RELATIONS);
    }

    /**
     * Update TenantSupportTicket.
     *
     * @param TenantSupportTicket $tenantSupportTicket The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(TenantSupportTicket $tenantSupportTicket, array $data): TenantSupportTicket
    {
        $tenantSupportTicket->update($data);

        return $tenantSupportTicket->fresh(self::LIST_RELATIONS);
    }

    /**
     * Mark a support ticket as resolved.
     *
     * @param TenantSupportTicket $supportTicket The ticket to resolve.
     */
    public function resolve(TenantSupportTicket $supportTicket): TenantSupportTicket
    {
        $supportTicket->update([
            'status' => SupportTicketStatus::Resolved->value,
            'resolved_at' => now(),
        ]);

        return $supportTicket->fresh(self::LIST_RELATIONS);
    }

    /**
     * Get open tickets with tenant and assignee.
     *
     * @return Collection<int, TenantSupportTicket>
     */
    public function getOpenWithDetails(): Collection
    {
        return TenantSupportTicket::query()->with(['tenant', 'assignee'])
            ->where('status', SupportTicketStatus::Open->value)
            ->get();
    }

    /**
     * KPI card metrics for support tickets.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $query = TenantSupportTicket::query()->forTenant();

        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $openStatuses = [
            SupportTicketStatus::Open->value,
            SupportTicketStatus::InProgress->value,
            SupportTicketStatus::WaitingCustomer->value,
        ];

        $openCount = (clone $query)->whereIn('status', $openStatuses)->count();

        $urgentOpen = (clone $query)
            ->whereIn('status', $openStatuses)
            ->where('priority', SupportTicketPriority::Urgent->value)
            ->count();

        return [
            ['key' => 'total', 'label' => 'Total Tickets', 'value' => (int)$counts->sum()],
            ['key' => 'open', 'label' => 'Open', 'value' => $openCount],
            ['key' => 'resolved', 'label' => 'Resolved', 'value' => (int)($counts[SupportTicketStatus::Resolved->value] ?? 0)],
            ['key' => 'closed', 'label' => 'Closed', 'value' => (int)($counts[SupportTicketStatus::Closed->value] ?? 0)],
            ['key' => 'urgent_open', 'label' => 'Urgent Open', 'value' => $urgentOpen],
        ];
    }
}
