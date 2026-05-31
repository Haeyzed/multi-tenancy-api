<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\TenantSupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantSupportMessage
 */
class TenantSupportMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unique identifier.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * Identifier of the parent support ticket.
             *
             * @example 10
             */
            'ticket_id' => $this->ticket_id,

            /**
             * Type of entity that sent the message.
             *
             * @example "admin"
             */
            'sender_type' => $this->sender_type,

            /**
             * Identifier of the sending entity.
             *
             * @example 7
             */
            'sender_id' => $this->sender_id,

            /**
             * Message body content.
             *
             * @example "We have applied a fix on our end. Please try again."
             */
            'body' => $this->body,

            /**
             * Whether the message is visible only to support staff.
             *
             * @example false
             *
             * @default false
             */
            'is_internal' => (bool) $this->is_internal,

            /**
             * Whether the message has been read by the recipient.
             *
             * @example true
             *
             * @default false
             */
            'is_read' => (bool) $this->is_read,

            /**
             * Timestamp when the message was read.
             *
             * @example "2026-05-30T15:00:00+00:00"
             *
             * @default null
             */
            'read_at' => $this->read_at?->toIso8601String(),

            /**
             * Timestamp when the message was created.
             *
             * @example "2026-05-30T14:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the message was last updated.
             *
             * @example "2026-05-30T14:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Parent ticket when eager loaded.
             *
             * @default null
             */
            'ticket' => new TenantSupportTicketResource($this->whenLoaded('ticket')),

            /**
             * Sending user when eager loaded.
             *
             * @default null
             */
            'sender' => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
