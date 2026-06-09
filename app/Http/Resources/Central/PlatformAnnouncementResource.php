<?php

declare(strict_types=1);

namespace App\Http\Resources\Central;

use App\Models\Central\Plan;
use App\Models\Central\PlatformAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PlatformAnnouncement
 */
class PlatformAnnouncementResource extends JsonResource
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
             * Announcement headline.
             *
             * @example "Scheduled maintenance"
             */
            'title' => $this->title,

            /**
             * Full announcement content.
             *
             * @example "The platform will be unavailable on June 1 from 02:00–04:00 UTC."
             */
            'body' => $this->body,

            /**
             * Announcement type.
             *
             * @example "maintenance"
             */
            'type' => $this->type,

            /**
             * Audience segment the announcement targets.
             *
             * @example "all"
             */
            'target_audience' => $this->target_audience,

            /**
             * Plan IDs the announcement applies to.
             *
             * @example ["019ea79b-10da-70cb-a9be-8f281325e639"]
             *
             * @default null
             */
            'target_plans' => $this->target_plans,

            /**
             * Human-readable plan names aligned with target_plans.
             *
             * @example ["Professional","Enterprise"]
             *
             * @default null
             */
            'target_plan_names' => $this->when(
                filled($this->target_plans),
                function (): array {
                    if (filled($this->target_plan_names)) {
                        return $this->target_plan_names;
                    }

                    $namesById = Plan::query()
                        ->whereIn('id', $this->target_plans)
                        ->pluck('name', 'id');

                    return collect($this->target_plans)
                        ->map(fn (string $id): ?string => $namesById[$id] ?? null)
                        ->filter()
                        ->values()
                        ->all();
                },
            ),

            /**
             * Whether the announcement is currently active.
             *
             * @example true
             *
             * @default false
             */
            'is_active' => (bool) $this->is_active,

            /**
             * Timestamp when the announcement becomes visible.
             *
             * @example "2026-06-01T00:00:00+00:00"
             *
             * @default null
             */
            'starts_at' => $this->starts_at?->toIso8601String(),

            /**
             * Timestamp when the announcement stops being visible.
             *
             * @example "2026-06-02T00:00:00+00:00"
             *
             * @default null
             */
            'ends_at' => $this->ends_at?->toIso8601String(),

            /**
             * Timestamp when the announcement was created.
             *
             * @example "2026-05-28T00:00:00+00:00"
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * Timestamp when the announcement was last updated.
             *
             * @example "2026-05-28T12:00:00+00:00"
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
