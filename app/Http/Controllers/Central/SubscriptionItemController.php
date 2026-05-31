<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreSubscriptionItemRequest;
use App\Http\Requests\Central\UpdateSubscriptionItemRequest;
use App\Http\Resources\Central\SubscriptionItemResource;
use App\Models\Central\SubscriptionItem;
use App\Services\Central\SubscriptionItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Line items on tenant subscriptions.
 */
class SubscriptionItemController extends Controller
{
    public function __construct(
        private readonly SubscriptionItemService $service,
    ) {}

    /**
     * Get paginated SubscriptionItem records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $items = $this->service->getPaginated($perPage);

        return $this->paginated($items, SubscriptionItemResource::collection($items));
    }

    /**
     * Create a new SubscriptionItem.
     *
     * @param  StoreSubscriptionItemRequest  $request  Validated request payload.
     */
    public function store(StoreSubscriptionItemRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new SubscriptionItemResource($item), 'Subscription item created successfully.');
    }

    /**
     * Find SubscriptionItem by route binding.
     *
     * @param  SubscriptionItem  $subscriptionItem  SubscriptionItem instance.
     */
    public function show(SubscriptionItem $subscriptionItem): JsonResponse
    {
        return $this->success(new SubscriptionItemResource($subscriptionItem));
    }

    /**
     * Update SubscriptionItem.
     *
     * @param  UpdateSubscriptionItemRequest  $request  Validated request payload.
     * @param  SubscriptionItem  $subscriptionItem  SubscriptionItem instance.
     */
    public function update(UpdateSubscriptionItemRequest $request, SubscriptionItem $subscriptionItem): JsonResponse
    {
        $item = $this->service->update($subscriptionItem, $request->validated());

        return $this->updated(new SubscriptionItemResource($item), 'Subscription item updated successfully.');
    }

    /**
     * Delete SubscriptionItem.
     *
     * @param  SubscriptionItem  $subscriptionItem  SubscriptionItem instance.
     */
    public function destroy(SubscriptionItem $subscriptionItem): JsonResponse
    {
        $this->service->delete($subscriptionItem);

        return $this->deleted('Subscription item deleted successfully.');
    }
}
