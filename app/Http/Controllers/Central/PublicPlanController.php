<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Resources\Central\PlanResource;
use App\Services\Central\PlanService;
use Illuminate\Http\JsonResponse;

/**
 * Public pricing plans for self-service signup.
 */
class PublicPlanController extends Controller
{
    public function __construct(
        private readonly PlanService $service,
    ) {}

    /**
     * List active public plans available for self-service signup.
     */
    public function index(): JsonResponse
    {
        $plans = $this->service->getPublicPlans()->load('planFeatures');

        return $this->success(PlanResource::collection($plans));
    }
}
