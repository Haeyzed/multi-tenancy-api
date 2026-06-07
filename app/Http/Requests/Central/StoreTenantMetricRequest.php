<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new tenant daily metrics snapshot.
 */
class StoreTenantMetricRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * UUID of the tenant these metrics belong to.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * Date for which these metrics were aggregated.
             *
             * @var string $metric_date
             *
             * @example "2026-01-15"
             */
            'metric_date' => 'required|date',

            /**
             * Total number of orders placed on this date; optional.
             *
             * @var int $total_orders
             *
             * @example 142
             */
            'total_orders' => 'sometimes|integer|min:0',

            /**
             * Total revenue in base currency units; optional.
             *
             * @var float $total_revenue
             *
             * @example 12500.50
             */
            'total_revenue' => 'sometimes|numeric|min:0',

            /**
             * Total number of products in the catalog; optional.
             *
             * @var int $total_products
             *
             * @example 350
             */
            'total_products' => 'sometimes|integer|min:0',

            /**
             * Total number of registered customers; optional.
             *
             * @var int $total_customers
             *
             * @example 1200
             */
            'total_customers' => 'sometimes|integer|min:0',

            /**
             * Storage consumed in megabytes; optional.
             *
             * @var int $storage_used_mb
             *
             * @example 512
             */
            'storage_used_mb' => 'sometimes|integer|min:0',

            /**
             * Bandwidth consumed in megabytes; optional.
             *
             * @var int $bandwidth_used_mb
             *
             * @example 2048
             */
            'bandwidth_used_mb' => 'sometimes|integer|min:0',

            /**
             * Number of API calls made on this date; optional.
             *
             * @var int $api_calls
             *
             * @example 8500
             */
            'api_calls' => 'sometimes|integer|min:0',
        ];
    }
}
