<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates query parameters for the dashboard overview endpoint.
 */
class DashboardOverviewRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ];
    }
}
