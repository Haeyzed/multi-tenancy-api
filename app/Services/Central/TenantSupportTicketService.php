<?php

declare(strict_types=1);

namespace App\Services\Central;

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
     * @return Collection<int, TenantSupportTicket>
     */
    public function getAll(): Collection
    {
        return TenantSupportTicket::query()->get();
    }

    /**
     * Get paginated TenantSupportTicket records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, TenantSupportTicket>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return TenantSupportTicket::query()->paginate($perPage);
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
}
