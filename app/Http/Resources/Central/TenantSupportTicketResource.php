<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\TenantSupportTicket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantSupportTicket
 */
class TenantSupportTicketResource extends JsonResource
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
             * Identifier of the submitting tenant.
             *
             * @example 42
             */
            'tenant_id' => $this->tenant_id,

            /**
             * Ticket category.
             *
             * @example "billing"
             */
            'category' => $this->category,

            /**
             * Ticket priority level.
             *
             * @example "high"
             */
            'priority' => $this->priority,

            /**
             * Current ticket status.
             *
             * @example "open"
             */
            'status' => $this->status,

            /**
             * Brief summary of the issue.
             *
             * @example "Unable to update payment method"
             */
            'subject' => $this->subject,

            /**
             * Full description of the issue.
             *
             * @example "We receive an error when saving a new card."
             */
            'body' => $this->body,

            /**
             * Identifier of the assigned support agent.
             *
             * @example 7
             *
             * @default null
             */
            'assigned_to' => $this->assigned_to,

            /**
             * Timestamp when the ticket was resolved.
             *
             * @example "2026-05-31T10:00:00+00:00"
             *
             * @default null
             */
            'resolved_at' => $this->resolved_at?->toIso8601String(),

            /**
             * Timestamp when the ticket was created.
             *
             * @example "2026-05-30T09:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the ticket was last updated.
             *
             * @example "2026-05-30T14:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Submitting tenant when eager loaded.
             *
             * @default null
             */
            'tenant' => new TenantResource($this->whenLoaded('tenant')),

            /**
             * Assigned support agent when eager loaded.
             *
             * @default null
             */
            'assignee' => new UserResource($this->whenLoaded('assignee')),

            /**
             * Ticket messages when eager loaded.
             *
             * @default null
             */
            'messages' => TenantSupportMessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
