<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant health check result.
 */
class UpdateTenantHealthCheckRequest extends BaseRequest
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
             * UUID of the tenant being health-checked; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * Type of infrastructure check performed; optional on update.
             *
             * @var string $check_type
             *
             * @example "redis"
             */
            'check_type' => 'sometimes|string|in:db_connectivity,storage,queue,ssl,redis,search',

            /**
             * Overall result status of the health check; optional on update.
             *
             * @var string $status
             *
             * @example "warning"
             */
            'status' => 'sometimes|string|in:healthy,warning,critical,unknown',

            /**
             * Response time of the check in milliseconds; nullable.
             *
             * @var int|null $response_time_ms
             *
             * @example 850
             */
            'response_time_ms' => 'nullable|integer|min:0',

            /**
             * Human-readable details about the check outcome; nullable.
             *
             * @var string|null $message
             *
             * @example "Redis latency above threshold"
             */
            'message' => 'nullable|string',

            /**
             * Timestamp when the health check was performed; optional on update.
             *
             * @var string $checked_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'checked_at' => 'sometimes|date',
        ];
    }
}
