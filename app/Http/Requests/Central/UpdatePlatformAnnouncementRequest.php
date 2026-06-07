<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing platform announcement.
 */
class UpdatePlatformAnnouncementRequest extends BaseRequest
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
             * Short headline displayed for the announcement; optional on update.
             *
             * @var string $title
             *
             * @example "Scheduled Maintenance"
             */
            'title' => 'sometimes|string|max:255',

            /**
             * Full announcement content shown to users; optional on update.
             *
             * @var string $body
             *
             * @example "We will perform maintenance on Sunday from 2–4 AM UTC."
             */
            'body' => 'sometimes|string',

            /**
             * Category of announcement for styling and filtering; optional on update.
             *
             * @var string $type
             *
             * @example "alert"
             */
            'type' => 'sometimes|string|in:maintenance,feature,alert,info',

            /**
             * Audience segment that should see this announcement; optional on update.
             *
             * @var string $target_audience
             *
             * @example "admins_only"
             */
            'target_audience' => 'sometimes|string|in:all,plan_specific,admins_only',

            /**
             * List of plan slugs when target_audience is plan_specific; optional.
             *
             * @var array<int, string> $target_plans
             *
             * @example ["enterprise"]
             */
            'target_plans' => 'sometimes|array',

            /**
             * Whether the announcement is currently visible; optional.
             *
             * @var bool $is_active
             *
             * @example false
             */
            'is_active' => 'sometimes|boolean',

            /**
             * When the announcement becomes visible; nullable for immediate display.
             *
             * @var string|null $starts_at
             *
             * @example "2026-02-01T00:00:00Z"
             */
            'starts_at' => 'nullable|date',

            /**
             * When the announcement should stop being shown; nullable.
             *
             * @var string|null $ends_at
             *
             * @example "2026-02-28T23:59:59Z"
             */
            'ends_at' => 'nullable|date',
        ];
    }
}
