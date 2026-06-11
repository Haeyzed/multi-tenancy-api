<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\TenantBootstrapService;
use Illuminate\Http\JsonResponse;

/**
 * Public tenant bootstrap payload for storefront/admin clients.
 */
class BootstrapController extends Controller
{
    public function __construct(
        private readonly TenantBootstrapService $service,
    ) {}

    /**
     * Get public branding and tenant identity for the current domain.
     */
    public function show(): JsonResponse
    {
        return $this->success(
            $this->service->getPublicPayload(),
            'Tenant bootstrap retrieved successfully.',
        );
    }
}
