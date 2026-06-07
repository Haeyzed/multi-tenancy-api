<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreSubscriptionEventRequest;
use App\Http\Requests\Central\UpdateSubscriptionEventRequest;
use App\Http\Resources\Central\SubscriptionEventResource;
use App\Models\Central\SubscriptionEvent;
use App\Services\Central\SubscriptionEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Audit events on tenant subscriptions.
 */
class SubscriptionEventController extends Controller
{
    public function __construct(
        private readonly SubscriptionEventService $service,
    ) {}

    /**
     * Get paginated SubscriptionEvent records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, SubscriptionEventResource::collection($items), 'Subscription events retrieved successfully.');
    }

    /**
     * Create a new SubscriptionEvent.
     *
     * @param  StoreSubscriptionEventRequest  $request  Validated request payload.
     */
    public function store(StoreSubscriptionEventRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new SubscriptionEventResource($item), 'Subscription event created successfully.');
    }

    /**
     * Find SubscriptionEvent by route binding.
     *
     * @param  SubscriptionEvent  $subscriptionEvent  SubscriptionEvent instance.
     */
    public function show(SubscriptionEvent $subscriptionEvent): JsonResponse
    {
        return $this->success(new SubscriptionEventResource($subscriptionEvent), 'Subscription event retrieved successfully.');
    }

    /**
     * Update SubscriptionEvent.
     *
     * @param  UpdateSubscriptionEventRequest  $request  Validated request payload.
     * @param  SubscriptionEvent  $subscriptionEvent  SubscriptionEvent instance.
     */
    public function update(UpdateSubscriptionEventRequest $request, SubscriptionEvent $subscriptionEvent): JsonResponse
    {
        $item = $this->service->update($subscriptionEvent, $request->validated());

        return $this->updated(new SubscriptionEventResource($item), 'Subscription event updated successfully.');
    }

    /**
     * Delete SubscriptionEvent.
     *
     * @param  SubscriptionEvent  $subscriptionEvent  SubscriptionEvent instance.
     */
    public function destroy(SubscriptionEvent $subscriptionEvent): JsonResponse
    {
        $this->service->delete($subscriptionEvent);

        return $this->deleted('Subscription event deleted successfully.');
    }
}
