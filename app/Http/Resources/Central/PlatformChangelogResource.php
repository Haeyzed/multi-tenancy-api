<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\PlatformChangelog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlatformChangelog
 */
class PlatformChangelogResource extends JsonResource
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
             * Semantic version string.
             *
             * @example "1.2.0"
             */
            'version' => $this->version,

            /**
             * Short headline for the release.
             *
             * @example "Billing improvements"
             */
            'title' => $this->title,

            /**
             * Detailed release notes.
             *
             * @example "Added proration support for plan changes."
             */
            'description' => $this->description,

            /**
             * Type of changelog entry.
             *
             * @example "feature"
             */
            'type' => $this->type,

            /**
             * Whether the entry is visible to users.
             *
             * @example true
             *
             * @default false
             */
            'is_published' => (bool)$this->is_published,

            /**
             * Timestamp when the entry was published.
             *
             * @example "2026-05-01T00:00:00+00:00"
             *
             * @default null
             */
            'published_at' => $this->published_at?->toIso8601String(),

            /**
             * Timestamp when the entry was created.
             *
             * @example "2026-04-28T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the entry was last updated.
             *
             * @example "2026-05-01T00:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
