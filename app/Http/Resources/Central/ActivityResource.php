<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Activity
 */
class ActivityResource extends JsonResource
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
             * Log channel or category name.
             *
             * @example "default"
             *
             * @default null
             */
            'log_name' => $this->log_name,

            /**
             * Human-readable description of the activity.
             *
             * @example "User updated tenant settings"
             */
            'description' => $this->description,

            /**
             * Fully qualified class name of the subject model.
             *
             * @example "App\\Models\\Central\\Tenant"
             *
             * @default null
             */
            'subject_type' => $this->subject_type,

            /**
             * Primary key of the subject model.
             *
             * @example 42
             *
             * @default null
             */
            'subject_id' => $this->subject_id,

            /**
             * Fully qualified class name of the causer model.
             *
             * @example "App\\Models\\Central\\User"
             *
             * @default null
             */
            'causer_type' => $this->causer_type,

            /**
             * Primary key of the causer model.
             *
             * @example 7
             *
             * @default null
             */
            'causer_id' => $this->causer_id,

            /**
             * Event name that triggered the activity.
             *
             * @example "updated"
             *
             * @default null
             */
            'event' => $this->event,

            /**
             * Old and new attribute values for the logged change.
             *
             * @example {"attributes":{"name":"Acme"},"old":{"name":"Acme Corp"}}
             *
             * @default null
             */
            'attribute_changes' => $this->attribute_changes,

            /**
             * Additional metadata stored with the activity.
             *
             * @example {"ip":"127.0.0.1"}
             *
             * @default null
             */
            'properties' => $this->properties,

            /**
             * Timestamp when the activity was recorded.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the activity was last updated.
             *
             * @example "2026-01-15T10:30:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Subject model summary when eager loaded.
             *
             * @example {"id":42,"type":"App\\Models\\Central\\Tenant"}
             *
             * @default null
             */
            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->getKey(),
                'type' => $this->subject_type,
            ] : null),

            /**
             * Causer model summary when eager loaded.
             *
             * @example {"id":7,"type":"App\\Models\\Central\\User"}
             *
             * @default null
             */
            'causer' => $this->whenLoaded('causer', fn () => $this->causer ? [
                'id' => $this->causer->getKey(),
                'type' => $this->causer_type,
            ] : null),
        ];
    }
}
