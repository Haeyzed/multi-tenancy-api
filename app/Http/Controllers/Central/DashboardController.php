<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Central\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function index(Request $request): JsonResponse
    {
        $overview = $this->service->getOverview($request->user());

        return $this->success($overview, 'Dashboard overview retrieved successfully.');
    }
}
