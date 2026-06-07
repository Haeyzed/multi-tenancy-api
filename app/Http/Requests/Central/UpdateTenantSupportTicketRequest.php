<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing tenant support ticket.
 */
class UpdateTenantSupportTicketRequest extends BaseRequest
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
             * UUID of the tenant that opened this ticket; optional on update.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'sometimes|uuid|exists:tenants,id',

            /**
             * Category of the support request; optional on update.
             *
             * @var string $category
             *
             * @example "billing"
             */
            'category' => 'sometimes|string|in:billing,technical,general,feature_request',

            /**
             * Priority level assigned to the ticket; optional on update.
             *
             * @var string $priority
             *
             * @example "urgent"
             */
            'priority' => 'sometimes|string|in:low,medium,high,urgent',

            /**
             * Current workflow status of the ticket; optional on update.
             *
             * @var string $status
             *
             * @example "resolved"
             */
            'status' => 'sometimes|string|in:open,in_progress,waiting_customer,resolved,closed',

            /**
             * Brief summary of the support issue; optional on update.
             *
             * @var string $subject
             *
             * @example "Unable to connect payment gateway"
             */
            'subject' => 'sometimes|string|max:255',

            /**
             * Detailed description of the support issue; optional on update.
             *
             * @var string $body
             *
             * @example "Issue resolved after updating webhook secret."
             */
            'body' => 'sometimes|string',

            /**
             * ID of the support agent assigned to this ticket; nullable.
             *
             * @var int|null $assigned_to
             *
             * @example 3
             */
            'assigned_to' => 'nullable|exists:users,id',

            /**
             * Timestamp when the ticket was marked resolved; nullable.
             *
             * @var string|null $resolved_at
             *
             * @example "2026-01-16T14:00:00Z"
             */
            'resolved_at' => 'nullable|date',
        ];
    }
}
