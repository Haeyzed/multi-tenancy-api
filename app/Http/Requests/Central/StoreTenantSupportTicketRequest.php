<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new tenant support ticket.
 */
class StoreTenantSupportTicketRequest extends FormRequest
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
             * UUID of the tenant that opened this ticket.
             *
             * @var string $tenant_id
             *
             * @example "550e8400-e29b-41d4-a716-446655440000"
             */
            'tenant_id' => 'required|uuid|exists:tenants,id',

            /**
             * Category of the support request.
             *
             * @var string $category
             *
             * @example "technical"
             */
            'category' => 'required|string|in:billing,technical,general,feature_request',

            /**
             * Priority level assigned to the ticket.
             *
             * @var string $priority
             *
             * @example "high"
             */
            'priority' => 'required|string|in:low,medium,high,urgent',

            /**
             * Current workflow status of the ticket.
             *
             * @var string $status
             *
             * @example "open"
             */
            'status' => 'required|string|in:open,in_progress,waiting_customer,resolved,closed',

            /**
             * Brief summary of the support issue.
             *
             * @var string $subject
             *
             * @example "Unable to connect payment gateway"
             */
            'subject' => 'required|string|max:255',

            /**
             * Detailed description of the support issue.
             *
             * @var string $body
             *
             * @example "Stripe webhook returns 500 when processing checkout events."
             */
            'body' => 'required|string',

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
