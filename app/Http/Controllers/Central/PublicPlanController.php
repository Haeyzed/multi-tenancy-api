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
    )
    {
    }

    /**
     * List active public plans with pricing and display copy for signup.
     */
    public function index(): JsonResponse
    {
        return $this->success(
            PlanResource::collection($this->service->getPublicPlans()),
            'Public plans retrieved successfully.',
        );
    }
}
