<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\DashboardOverviewRequest;
use App\Services\Central\DashboardService;
use Illuminate\Http\JsonResponse;

/**
 * Platform dashboard overview.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
    ) {}

    /**
     * Aggregated KPI cards, charts, and recent records for the dashboard.
     */
    public function index(DashboardOverviewRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $overview = $this->service->getOverview(
            $request->user(),
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
        );

        return $this->success($overview, 'Dashboard overview retrieved successfully.');
    }
}
