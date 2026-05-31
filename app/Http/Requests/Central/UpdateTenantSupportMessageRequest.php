<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for updating an existing support ticket message.
 */
class UpdateTenantSupportMessageRequest extends FormRequest
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
             * ID of the support ticket this message belongs to; optional on update.
             *
             * @var int $ticket_id
             *
             * @example 42
             */
            'ticket_id' => 'sometimes|exists:tenant_support_tickets,id',

            /**
             * Type of entity that sent this message; optional on update.
             *
             * @var string $sender_type
             *
             * @example "user"
             */
            'sender_type' => 'sometimes|string|in:user,system,admin',

            /**
             * ID of the sending user; nullable for system messages.
             *
             * @var int|null $sender_id
             *
             * @example 5
             */
            'sender_id' => 'nullable|exists:users,id',

            /**
             * Message content body; optional on update.
             *
             * @var string $body
             *
             * @example "Thank you for the update."
             */
            'body' => 'sometimes|string',

            /**
             * Whether this message is visible only to support staff; optional.
             *
             * @var bool $is_internal
             *
             * @example true
             */
            'is_internal' => 'sometimes|boolean',

            /**
             * Whether the recipient has read this message; optional.
             *
             * @var bool $is_read
             *
             * @example true
             */
            'is_read' => 'sometimes|boolean',

            /**
             * Timestamp when the message was read; nullable.
             *
             * @var string|null $read_at
             *
             * @example "2026-01-15T11:00:00Z"
             */
            'read_at' => 'nullable|date',
        ];
    }
}
