<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantSupportMessageRequest;
use App\Http\Requests\Central\UpdateTenantSupportMessageRequest;
use App\Http\Resources\Central\TenantSupportMessageResource;
use App\Models\Central\TenantSupportMessage;
use App\Services\Central\TenantSupportMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Messages on tenant support tickets.
 */
class TenantSupportMessageController extends Controller
{
    public function __construct(
        private readonly TenantSupportMessageService $service,
    ) {}

    /**
     * Get paginated TenantSupportMessage records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, TenantSupportMessageResource::collection($items));
    }

    /**
     * Create a new TenantSupportMessage.
     *
     * @param  StoreTenantSupportMessageRequest  $request  Validated request payload.
     */
    public function store(StoreTenantSupportMessageRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantSupportMessageResource($item), 'Support message created successfully.');
    }

    /**
     * Find TenantSupportMessage by route binding.
     *
     * @param  TenantSupportMessage  $supportMessage  TenantSupportMessage instance.
     */
    public function show(TenantSupportMessage $supportMessage): JsonResponse
    {
        return $this->success(new TenantSupportMessageResource($supportMessage));
    }

    /**
     * Update TenantSupportMessage.
     *
     * @param  UpdateTenantSupportMessageRequest  $request  Validated request payload.
     * @param  TenantSupportMessage  $supportMessage  TenantSupportMessage instance.
     */
    public function update(UpdateTenantSupportMessageRequest $request, TenantSupportMessage $supportMessage): JsonResponse
    {
        $item = $this->service->update($supportMessage, $request->validated());

        return $this->updated(new TenantSupportMessageResource($item), 'Support message updated successfully.');
    }

    /**
     * Delete TenantSupportMessage.
     *
     * @param  TenantSupportMessage  $supportMessage  TenantSupportMessage instance.
     */
    public function destroy(TenantSupportMessage $supportMessage): JsonResponse
    {
        $this->service->delete($supportMessage);

        return $this->deleted('Support message deleted successfully.');
    }

    /**
     * Mark a support message as read.
     *
     * @param  TenantSupportMessage  $supportMessage  TenantSupportMessage instance.
     */
    public function markAsRead(TenantSupportMessage $supportMessage): JsonResponse
    {
        $item = $this->service->markAsRead($supportMessage);

        return $this->success(new TenantSupportMessageResource($item), 'Support message marked as read.');
    }
}
