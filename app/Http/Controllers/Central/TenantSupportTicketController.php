<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreTenantSupportTicketRequest;
use App\Http\Requests\Central\UpdateTenantSupportTicketRequest;
use App\Http\Resources\Central\TenantSupportTicketResource;
use App\Models\Central\TenantSupportTicket;
use App\Services\Central\TenantSupportTicketService;
use App\Support\QueryFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Support tickets submitted by tenants.
 */
class TenantSupportTicketController extends Controller
{
    public function __construct(
        private readonly TenantSupportTicketService $service,
    )
    {
    }

    /**
     * Get paginated TenantSupportTicket records.
     *
     * @param Request $request Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');
        $status = QueryFilter::parseList($request->query('status'));
        $priority = QueryFilter::parseList($request->query('priority'));
        $category = QueryFilter::parseList($request->query('category'));

        $items = $this->service->getPaginated($perPage, $search, $status, $priority, $category);

        return $this->paginated($items, TenantSupportTicketResource::collection($items), 'Support tickets retrieved successfully.');
    }

    /**
     * KPI card metrics for support tickets.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Support ticket KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new TenantSupportTicket.
     *
     * @param StoreTenantSupportTicketRequest $request Validated request payload.
     */
    public function store(StoreTenantSupportTicketRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new TenantSupportTicketResource($item), 'Support ticket created successfully.');
    }

    /**
     * Find TenantSupportTicket by route binding.
     *
     * @param TenantSupportTicket $supportTicket TenantSupportTicket instance.
     */
    public function show(TenantSupportTicket $supportTicket): JsonResponse
    {
        $item = $this->service->findOrFail($supportTicket->id);

        return $this->success(new TenantSupportTicketResource($item), 'Support ticket retrieved successfully.');
    }

    /**
     * Update TenantSupportTicket.
     *
     * @param UpdateTenantSupportTicketRequest $request Validated request payload.
     * @param TenantSupportTicket $supportTicket TenantSupportTicket instance.
     */
    public function update(UpdateTenantSupportTicketRequest $request, TenantSupportTicket $supportTicket): JsonResponse
    {
        $item = $this->service->update($supportTicket, $request->validated());

        return $this->updated(new TenantSupportTicketResource($item), 'Support ticket updated successfully.');
    }

    /**
     * Delete TenantSupportTicket.
     *
     * @param TenantSupportTicket $supportTicket TenantSupportTicket instance.
     */
    public function destroy(TenantSupportTicket $supportTicket): JsonResponse
    {
        $this->service->delete($supportTicket);

        return $this->deleted('Support ticket deleted successfully.');
    }

    /**
     * Assign a support ticket to a platform administrator.
     *
     * @param Request $request Must include `admin_id` of the assignee.
     * @param TenantSupportTicket $supportTicket TenantSupportTicket instance.
     */
    public function assign(Request $request, TenantSupportTicket $supportTicket): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|integer|exists:users,id',
        ]);

        $item = $this->service->assign($supportTicket, (int)$validated['admin_id']);

        return $this->success(new TenantSupportTicketResource($item), 'Support ticket assigned.');
    }

    /**
     * Mark a support ticket as resolved.
     *
     * @param TenantSupportTicket $supportTicket TenantSupportTicket instance.
     */
    public function resolve(TenantSupportTicket $supportTicket): JsonResponse
    {
        $item = $this->service->resolve($supportTicket);

        return $this->success(new TenantSupportTicketResource($item), 'Support ticket resolved.');
    }
}
