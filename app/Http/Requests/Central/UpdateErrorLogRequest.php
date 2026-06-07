<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing platform error log entry.
 */
class UpdateErrorLogRequest extends BaseRequest
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
             * UUID of the tenant associated with this error; nullable for platform-wide errors.
             *
             * @var string|null $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'nullable|uuid|exists:tenants,id',

            /**
             * Severity level of the error event; optional on update.
             *
             * @var string $severity
             *
             * @example "warning"
             */
            'severity' => 'sometimes|string|in:debug,info,warning,error,critical',

            /**
             * Logging channel or source identifier; optional on update.
             *
             * @var string $channel
             *
             * @example "queue"
             */
            'channel' => 'sometimes|string|max:255',

            /**
             * Human-readable error message; optional on update.
             *
             * @var string $message
             *
             * @example "Failed to process webhook payload"
             */
            'message' => 'sometimes|string',

            /**
             * Additional structured context for debugging; optional.
             *
             * @var array<string, mixed> $context
             *
             * @example {"exception": "RuntimeException", "line": 42}
             */
            'context' => 'sometimes|array',

            /**
             * Timestamp when the error occurred; optional on update.
             *
             * @var string $occurred_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'occurred_at' => 'sometimes|date',

            /**
             * Timestamp when the error was marked resolved; nullable.
             *
             * @var string|null $resolved_at
             *
             * @example "2026-01-15T12:00:00Z"
             */
            'resolved_at' => 'nullable|date',
        ];
    }
}
