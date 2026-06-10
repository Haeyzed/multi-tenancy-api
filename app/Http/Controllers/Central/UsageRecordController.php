<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreUsageRecordRequest;
use App\Http\Requests\Central\UpdateUsageRecordRequest;
use App\Http\Resources\Central\UsageRecordResource;
use App\Models\Central\UsageRecord;
use App\Services\Central\UsageRecordService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Metered usage records for billing.
 */
class UsageRecordController extends Controller
{
    public function __construct(
        private readonly UsageRecordService $service,
    ) {}

    /**
     * Get paginated UsageRecord records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $metrics = QueryFilter::parseList($request->query('metric'));

        $items = $this->service->getPaginated($perPage, $search, $metrics);

        return $this->paginated($items, UsageRecordResource::collection($items), 'Usage records retrieved successfully.');
    }

    /**
     * Create a new UsageRecord.
     *
     * @param  StoreUsageRecordRequest  $request  Validated request payload.
     */
    public function store(StoreUsageRecordRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new UsageRecordResource($item), 'Usage record created successfully.');
    }

    /**
     * Find UsageRecord by route binding.
     *
     * @param  UsageRecord  $usageRecord  UsageRecord instance.
     */
    public function show(UsageRecord $usageRecord): JsonResponse
    {
        return $this->success(
            new UsageRecordResource(
                $usageRecord->load(['tenant', 'subscription']),
            ),
            'Usage record retrieved successfully.',
        );
    }

    /**
     * Update UsageRecord.
     *
     * @param  UpdateUsageRecordRequest  $request  Validated request payload.
     * @param  UsageRecord  $usageRecord  UsageRecord instance.
     */
    public function update(UpdateUsageRecordRequest $request, UsageRecord $usageRecord): JsonResponse
    {
        $item = $this->service->update($usageRecord, $request->validated());

        return $this->updated(new UsageRecordResource($item), 'Usage record updated successfully.');
    }

    /**
     * Delete UsageRecord.
     *
     * @param  UsageRecord  $usageRecord  UsageRecord instance.
     */
    public function destroy(UsageRecord $usageRecord): JsonResponse
    {
        $this->service->delete($usageRecord);

        return $this->deleted('Usage record deleted successfully.');
    }
}
