<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates query parameters for KPI metrics endpoints.
 */
class KpiMetricsRequest extends BaseRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * Optional start of the reporting period (inclusive).
             *
             * @var string|null $start_date
             *
             * @example 2026-05-01
             */
            'start_date' => 'sometimes|date',

            /**
             * Optional end of the reporting period (inclusive).
             *
             * @var string|null $end_date
             *
             * @example 2026-05-31
             */
            'end_date' => 'sometimes|date|after_or_equal:start_date',

            /**
             * Optional tenant UUID to scope KPIs to a single tenant.
             *
             * @var string|null $tenant_id
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * Maximum number of top tenants to return when unscoped.
             *
             * @var int|null $limit
             */
            'limit' => 'sometimes|integer|min:1|max:25',
        ];
    }
}
