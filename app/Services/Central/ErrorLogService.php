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
     * Relations eager loaded for list and detail responses.
     *
     * @var list<string>
     */
    private const LIST_RELATIONS = [
        'tenant',
    ];

    /**
     * Get all ErrorLog records.
     *
     * @param string|null $search Optional search term.
     * @return Collection<int, ErrorLog>
     */
    public function getAll(?string $search = null): Collection
    {
        return ErrorLog::query()
            ->forTenant()
            ->search($search)
            ->get();
    }

    /**
     * @param list<string> $severity
     * @param list<string> $resolution
     */
    public function getPaginated(
        int     $perPage = 15,
        ?string $search = null,
        array   $severity = [],
        array   $resolution = [],
    ): LengthAwarePaginator
    {
        return ErrorLog::query()
            ->with(self::LIST_RELATIONS)
            ->forTenant()
            ->search($search)
            ->filterSeverity($severity)
            ->filterResolution($resolution)
            ->latest('occurred_at')
            ->paginate($perPage);
    }

    /**
     * Find ErrorLog by ID.
     *
     * @param int $id Record identifier.
     */
    public function find(int $id): ?ErrorLog
    {
        return ErrorLog::query()->find($id);
    }

    /**
     * Find ErrorLog by ID or fail.
     *
     * @param int $id Record identifier.
     */
    public function findOrFail(int $id): ErrorLog
    {
        return ErrorLog::query()->findOrFail($id);
    }

    /**
     * Create a new ErrorLog.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): ErrorLog
    {
        return ErrorLog::query()->create($data);
    }

    /**
     * Delete ErrorLog.
     *
     * @param ErrorLog $errorLog The model instance to delete.
     */
    public function delete(ErrorLog $errorLog): bool
    {
        return (bool)$errorLog->delete();
    }

    /**
     * Filter by tenant.
     *
     * @param string $tenantId Tenant UUID.
     * @return Collection<int, ErrorLog>
     */
    public function getByTenant(string $tenantId): Collection
    {
        return ErrorLog::query()->where('tenant_id', $tenantId)->get();
    }

    /**
     * Filter by severity.
     *
     * @param string $severity Error severity to filter by.
     * @return Collection<int, ErrorLog>
     */
    public function getBySeverity(string $severity): Collection
    {
        return ErrorLog::query()->where('severity', $severity)->get();
    }

    /**
     * Mark an error log entry as resolved.
     *
     * @param ErrorLog $errorLog The error log to resolve.
     */
    public function resolve(ErrorLog $errorLog): ErrorLog
    {
        $errorLog->update(['resolved_at' => now()]);

        return $errorLog->fresh(self::LIST_RELATIONS);
    }

    /**
     * Update ErrorLog.
     *
     * @param ErrorLog $errorLog The model instance to update.
     * @param array<string, mixed> $data Attribute data to persist.
     */
    public function update(ErrorLog $errorLog, array $data): ErrorLog
    {
        $errorLog->update($data);

        return $errorLog->fresh(self::LIST_RELATIONS);
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

    /**
     * KPI card metrics for error logs.
     *
     * @return list<array{key: string, label: string, value: int}>
     */
    public function getMetrics(): array
    {
        $query = ErrorLog::query()->forTenant();

        $counts = (clone $query)
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity');

        $unresolved = (clone $query)->whereNull('resolved_at')->count();

        $unresolvedCritical = (clone $query)
            ->whereNull('resolved_at')
            ->where('severity', ErrorLogSeverity::Critical->value)
            ->count();

        return [
            ['key' => 'total', 'label' => 'Total Errors', 'value' => (int)$counts->sum()],
            ['key' => 'unresolved', 'label' => 'Unresolved', 'value' => $unresolved],
            ['key' => 'unresolved_critical', 'label' => 'Unresolved Critical', 'value' => $unresolvedCritical],
            ['key' => 'critical', 'label' => 'Critical', 'value' => (int)($counts[ErrorLogSeverity::Critical->value] ?? 0)],
            ['key' => 'error', 'label' => 'Error', 'value' => (int)($counts[ErrorLogSeverity::Error->value] ?? 0)],
            ['key' => 'warning', 'label' => 'Warning', 'value' => (int)($counts[ErrorLogSeverity::Warning->value] ?? 0)],
        ];
    }
}
