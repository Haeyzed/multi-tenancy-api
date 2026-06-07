<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreErrorLogRequest;
use App\Http\Requests\Central\UpdateErrorLogRequest;
use App\Http\Resources\Central\ErrorLogResource;
use App\Models\Central\ErrorLog;
use App\Services\Central\ErrorLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Platform and tenant error logs.
 */
class ErrorLogController extends Controller
{
    public function __construct(
        private readonly ErrorLogService $service,
    ) {}

    /**
     * Get paginated ErrorLog records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $items = $this->service->getPaginated($perPage, $search);

        return $this->paginated($items, ErrorLogResource::collection($items), 'Error logs retrieved successfully.');
    }

    /**
     * KPI card metrics for error logs.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Error log KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new ErrorLog.
     *
     * @param  StoreErrorLogRequest  $request  Validated request payload.
     */
    public function store(StoreErrorLogRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new ErrorLogResource($item), 'Error log created successfully.');
    }

    /**
     * Find ErrorLog by route binding.
     *
     * @param  ErrorLog  $errorLog  ErrorLog instance.
     */
    public function show(ErrorLog $errorLog): JsonResponse
    {
        return $this->success(new ErrorLogResource($errorLog), 'Error log retrieved successfully.');
    }

    /**
     * Update ErrorLog.
     *
     * @param  UpdateErrorLogRequest  $request  Validated request payload.
     * @param  ErrorLog  $errorLog  ErrorLog instance.
     */
    public function update(UpdateErrorLogRequest $request, ErrorLog $errorLog): JsonResponse
    {
        $item = $this->service->update($errorLog, $request->validated());

        return $this->updated(new ErrorLogResource($item), 'Error log updated successfully.');
    }

    /**
     * Delete ErrorLog.
     *
     * @param  ErrorLog  $errorLog  ErrorLog instance.
     */
    public function destroy(ErrorLog $errorLog): JsonResponse
    {
        $this->service->delete($errorLog);

        return $this->deleted('Error log deleted successfully.');
    }

    /**
     * Mark an error log entry as resolved.
     *
     * @param  ErrorLog  $errorLog  ErrorLog instance.
     */
    public function resolve(ErrorLog $errorLog): JsonResponse
    {
        $item = $this->service->resolve($errorLog);

        return $this->success(new ErrorLogResource($item), 'Error log resolved.');
    }
}
