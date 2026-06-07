<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for creating a new platform error log entry.
 */
class StoreErrorLogRequest extends BaseRequest
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
             * Severity level of the error event.
             *
             * @var string $severity
             *
             * @example "error"
             */
            'severity' => 'required|string|in:debug,info,warning,error,critical',

            /**
             * Logging channel or source identifier.
             *
             * @var string $channel
             *
             * @example "queue"
             */
            'channel' => 'required|string|max:255',

            /**
             * Human-readable error message.
             *
             * @var string $message
             *
             * @example "Failed to process webhook payload"
             */
            'message' => 'required|string',

            /**
             * Additional structured context for debugging; optional.
             *
             * @var array<string, mixed> $context
             *
             * @example {"exception": "RuntimeException", "line": 42}
             */
            'context' => 'sometimes|array',

            /**
             * Timestamp when the error occurred.
             *
             * @var string $occurred_at
             *
             * @example "2026-01-15T10:30:00Z"
             */
            'occurred_at' => 'required|date',

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
