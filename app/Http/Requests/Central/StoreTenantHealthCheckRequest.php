<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new tenant health check result.
 */
class StoreTenantHealthCheckRequest extends FormRequest
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
             * UUID of the tenant being health-checked.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * Type of infrastructure check performed.
             *
             * @var string $check_type
             *
             * @example "db_connectivity"
             */
            'check_type' => 'required|string|in:db_connectivity,storage,queue,ssl,redis,search',

            /**
             * Overall result status of the health check.
             *
             * @var string $status
             *
             * @example "healthy"
             */
            'status' => 'required|string|in:healthy,warning,critical,unknown',

            /**
             * Response time of the check in milliseconds; nullable.
             *
             * @var int|null $response_time_ms
             *
             * @example 42
             */
            'response_time_ms' => 'nullable|integer|min:0',

            /**
             * Human-readable details about the check outcome; nullable.
             *
             * @var string|null $message
             *
             * @example "Database connection established in 42ms"
             */
            'message' => 'nullable|string',

            /**
             * Timestamp when the health check was performed.
             *
             * @var string $checked_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'checked_at' => 'required|date',
        ];
    }
}
