<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new support ticket message.
 */
class StoreTenantSupportMessageRequest extends FormRequest
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
             * ID of the support ticket this message belongs to.
             *
             * @var int $ticket_id
             *
             * @example 42
             */
            'ticket_id' => 'required|exists:tenant_support_tickets,id',

            /**
             * Type of entity that sent this message.
             *
             * @var string $sender_type
             *
             * @example "admin"
             */
            'sender_type' => 'required|string|in:user,system,admin',

            /**
             * ID of the sending user; nullable for system messages.
             *
             * @var int|null $sender_id
             *
             * @example 1
             */
            'sender_id' => 'nullable|exists:users,id',

            /**
             * Message content body.
             *
             * @var string $body
             *
             * @example "We have identified the issue and are working on a fix."
             */
            'body' => 'required|string',

            /**
             * Whether this message is visible only to support staff; optional.
             *
             * @var bool $is_internal
             *
             * @example false
             */
            'is_internal' => 'sometimes|boolean',

            /**
             * Whether the recipient has read this message; optional.
             *
             * @var bool $is_read
             *
             * @example false
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
