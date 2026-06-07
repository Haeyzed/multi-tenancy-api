<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\BulkDeleteSubscriptionsRequest;
use App\Http\Requests\Central\ChangePlanRequest;
use App\Http\Requests\Central\StoreSubscriptionRequest;
use App\Http\Requests\Central\UpdateSubscriptionRequest;
use App\Http\Resources\Central\SubscriptionResource;
use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Services\Central\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tenant subscription records.
 */
class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $service,
    ) {}

    /**
     * Get paginated Subscription records.
     *
     * @param  Request  $request  Incoming HTTP request.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->query('search');

        $items = $this->service->getPaginated($perPage, $search);

        return $this->paginated($items, SubscriptionResource::collection($items), 'Subscriptions retrieved successfully.');
    }

    /**
     * KPI card metrics for subscriptions.
     */
    public function metrics(): JsonResponse
    {
        return $this->success(
            ['cards' => $this->service->getMetrics()],
            'Subscription KPI metrics retrieved successfully.',
        );
    }

    /**
     * Create a new Subscription.
     *
     * @param  StoreSubscriptionRequest  $request  Validated request payload.
     */
    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $item = $this->service->create($request->validated());

        return $this->created(new SubscriptionResource($item), 'Subscription created successfully.');
    }

    /**
     * Find Subscription by route binding.
     *
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function show(Subscription $subscription): JsonResponse
    {
        $subscription->load([
            'tenant',
            'plan',
            'latestInvoice',
            'subscriptionItems',
            'lifecycleEvents',
        ]);

        return $this->success(new SubscriptionResource($subscription), 'Subscription retrieved successfully.');
    }

    /**
     * Update Subscription.
     *
     * @param  UpdateSubscriptionRequest  $request  Validated request payload.
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        $item = $this->service->update($subscription, $request->validated());

        return $this->updated(new SubscriptionResource($item), 'Subscription updated successfully.');
    }

    /**
     * Delete Subscription.
     *
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function destroy(Subscription $subscription): JsonResponse
    {
        $this->service->delete($subscription);

        return $this->deleted('Subscription deleted successfully.');
    }

    /**
     * Delete multiple subscriptions in one request.
     */
    public function bulkDestroy(BulkDeleteSubscriptionsRequest $request): JsonResponse
    {
        $deleted = $this->service->deleteMany($request->validated('ids'));

        return $this->success(
            ['deleted' => $deleted],
            "{$deleted} subscription(s) deleted successfully.",
        );
    }

    /**
     * Cancel an active subscription.
     *
     * @param  Request  $request  May include optional `reason` for cancellation.
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        $item = $this->service->cancel($subscription, $request->string('reason')->toString() ?: null);

        return $this->success(new SubscriptionResource($item), 'Subscription cancelled.');
    }

    /**
     * Renew a subscription billing period.
     *
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function renew(Subscription $subscription): JsonResponse
    {
        $item = $this->service->renew($subscription);

        return $this->success(new SubscriptionResource($item), 'Subscription renewed.');
    }

    /**
     * Upgrade subscription to a higher plan.
     *
     * @param  ChangePlanRequest  $request  Validated plan change payload.
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function upgrade(ChangePlanRequest $request, Subscription $subscription): JsonResponse
    {
        $plan = Plan::query()->findOrFail($request->string('plan_id')->toString());
        $item = $this->service->upgrade($subscription, $plan);

        return $this->success(new SubscriptionResource($item), 'Subscription upgraded.');
    }

    /**
     * Downgrade subscription to a lower plan.
     *
     * @param  ChangePlanRequest  $request  Validated plan change payload.
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function downgrade(ChangePlanRequest $request, Subscription $subscription): JsonResponse
    {
        $plan = Plan::query()->findOrFail($request->string('plan_id')->toString());
        $item = $this->service->downgrade($subscription, $plan);

        return $this->success(new SubscriptionResource($item), 'Subscription downgraded.');
    }

    /**
     * Reactivate a cancelled subscription.
     *
     * @param  Subscription  $subscription  Subscription instance.
     */
    public function reactivate(Subscription $subscription): JsonResponse
    {
        $item = $this->service->reactivate($subscription);

        return $this->success(new SubscriptionResource($item), 'Subscription reactivated.');
    }
}
