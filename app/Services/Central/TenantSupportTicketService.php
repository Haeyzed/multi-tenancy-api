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
     * Get all TenantSupportTicket records.
     *
     * @param  string|null  $search  Optional search term.
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
     * Get paginated TenantSupportTicket records.
     *
     * @param  int  $perPage  Number of records per page.
     * @param  string|null  $search  Optional search term.
     * @return LengthAwarePaginator<int, TenantSupportTicket>
     */
    public function getPaginated(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return TenantSupportTicket::query()
            ->forTenant()
            ->search($search)
            ->paginate($perPage);
    }

    /**
     * Find TenantSupportTicket by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?TenantSupportTicket
    {
        return TenantSupportTicket::query()->find($id);
    }

    /**
     * Find TenantSupportTicket by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): TenantSupportTicket
    {
        return TenantSupportTicket::query()->findOrFail($id);
    }

    /**
     * Create a new TenantSupportTicket.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TenantSupportTicket
    {
        return TenantSupportTicket::query()->create($data);
    }

    /**
     * Update TenantSupportTicket.
     *
     * @param  TenantSupportTicket  $tenantSupportTicket  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(TenantSupportTicket $tenantSupportTicket, array $data): TenantSupportTicket
    {
        $tenantSupportTicket->query()->update($data);

        return $tenantSupportTicket->fresh();
    }

    /**
     * Delete TenantSupportTicket.
     *
     * @param  TenantSupportTicket  $tenantSupportTicket  The model instance to delete.
     */
    public function delete(TenantSupportTicket $tenantSupportTicket): bool
    {
        return $tenantSupportTicket->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return TenantSupportTicket::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by status.
     *
     * @param  string  $status  Status value to filter by.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByStatus(string $status): Collection
    {
        return TenantSupportTicket::query()->where('status', $status)->get();
    }

    /**
     * Filter by category.
     *
     * @param  string  $category  Ticket category to filter by.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByCategory(string $category): Collection
    {
        return TenantSupportTicket::query()->where('category', $category)->get();
    }

    /**
     * Filter by priority.
     *
     * @param  string  $priority  Ticket priority to filter by.
     * @return Collection<int, TenantSupportTicket>
     */
    public function getByPriority(string $priority): Collection
    {
        return TenantSupportTicket::query()->where('priority', $priority)->get();
    }

    /**
     * Assign a support ticket to a platform administrator.
     *
     * @param  TenantSupportTicket  $supportTicket  The ticket to assign.
     * @param  int  $adminId  ID of the administrator assignee.
     */
    public function assign(TenantSupportTicket $supportTicket, int $adminId): TenantSupportTicket
    {
        $supportTicket->query()->update(['assigned_to' => $adminId]);

        return $supportTicket->fresh();
    }

    /**
     * Mark a support ticket as resolved.
     *
     * @param  TenantSupportTicket  $supportTicket  The ticket to resolve.
     */
    public function resolve(TenantSupportTicket $supportTicket): TenantSupportTicket
    {
        $supportTicket->query()->update([
            'status' => SupportTicketStatus::Resolved->value,
            'resolved_at' => now(),
        ]);

        return $supportTicket->fresh();
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
            ['key' => 'total', 'label' => 'Total Tickets', 'value' => (int) $counts->sum()],
            ['key' => 'open', 'label' => 'Open', 'value' => $openCount],
            ['key' => 'resolved', 'label' => 'Resolved', 'value' => (int) ($counts[SupportTicketStatus::Resolved->value] ?? 0)],
            ['key' => 'closed', 'label' => 'Closed', 'value' => (int) ($counts[SupportTicketStatus::Closed->value] ?? 0)],
            ['key' => 'urgent_open', 'label' => 'Urgent Open', 'value' => $urgentOpen],
        ];
    }
}
