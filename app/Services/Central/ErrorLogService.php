<?php

declare(strict_types=1);

namespace App\Services\Central;

use App\Enums\Central\ErrorLogSeverity;
use App\Models\Central\ErrorLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Central ErrorLog records and queries.
 */
class ErrorLogService
{
    /**
     * Get all ErrorLog records.
     *
     * @return Collection<int, ErrorLog>
     */
    public function getAll(): Collection
    {
        return ErrorLog::query()->get();
    }

    /**
     * Get paginated ErrorLog records.
     *
     * @param  int  $perPage  Number of records per page.
     * @return LengthAwarePaginator<int, ErrorLog>
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return ErrorLog::query()->paginate($perPage);
    }

    /**
     * Find ErrorLog by ID.
     *
     * @param  int  $id  Record identifier.
     */
    public function find(int $id): ?ErrorLog
    {
        return ErrorLog::query()->find($id);
    }

    /**
     * Find ErrorLog by ID or fail.
     *
     * @param  int  $id  Record identifier.
     */
    public function findOrFail(int $id): ErrorLog
    {
        return ErrorLog::query()->findOrFail($id);
    }

    /**
     * Create a new ErrorLog.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ErrorLog
    {
        return ErrorLog::query()->create($data);
    }

    /**
     * Update ErrorLog.
     *
     * @param  ErrorLog  $errorLog  The model instance to update.
     * @param  array<string, mixed>  $data  Attribute data to persist.
     */
    public function update(ErrorLog $errorLog, array $data): ErrorLog
    {
        $errorLog->query()->update($data);

        return $errorLog->fresh();
    }

    /**
     * Delete ErrorLog.
     *
     * @param  ErrorLog  $errorLog  The model instance to delete.
     */
    public function delete(ErrorLog $errorLog): bool
    {
        return $errorLog->query()->delete() > 0;
    }

    /**
     * Filter by tenant.
     *
     * @param  string  $tenantId  Tenant UUID.
     * @return Collection<int, ErrorLog>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return ErrorLog::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by severity.
     *
     * @param  string  $severity  Error severity to filter by.
     * @return Collection<int, ErrorLog>
     */
    public function getBySeverity(string $severity): Collection
    {
        return ErrorLog::query()->where('severity', $severity)->get();
    }

    /**
     * Mark an error log entry as resolved.
     *
     * @param  ErrorLog  $errorLog  The error log to resolve.
     */
    public function resolve(ErrorLog $errorLog): ErrorLog
    {
        $errorLog->query()->update(['resolved_at' => now()]);

        return $errorLog->fresh();
    }

    /**
     * Get unresolved errors.
     *
     * @return Collection<int, ErrorLog>
     */
    public function getUnresolved(): Collection
    {
        return ErrorLog::query()->whereNull('resolved_at')
            ->whereIn('severity', [ErrorLogSeverity::Error->value, ErrorLogSeverity::Critical->value])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
