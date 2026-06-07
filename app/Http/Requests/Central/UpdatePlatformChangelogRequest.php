<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

/**
 * Validates incoming data for updating an existing platform changelog entry.
 */
class UpdatePlatformChangelogRequest extends BaseRequest
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
             * Semantic version string for this release; optional on update.
             *
             * @var string $version
             *
             * @example "2.4.1"
             */
            'version' => 'sometimes|string|max:50',

            /**
             * Short title summarizing the release; optional on update.
             *
             * @var string $title
             *
             * @example "Multi-currency support"
             */
            'title' => 'sometimes|string|max:255',

            /**
             * Detailed description of changes in this release; optional on update.
             *
             * @var string $description
             *
             * @example "Added support for EUR and GBP billing currencies."
             */
            'description' => 'sometimes|string',

            /**
             * Category of change for filtering and display; optional on update.
             *
             * @var string $type
             *
             * @example "fix"
             */
            'type' => 'sometimes|string|in:feature,fix,breaking,security,performance',

            /**
             * Whether the entry is visible to tenants; optional.
             *
             * @var bool $is_published
             *
             * @example true
             */
            'is_published' => 'sometimes|boolean',

            /**
             * Timestamp when the entry was or will be published; nullable.
             *
             * @var string|null $published_at
             *
             * @example "2026-01-20T09:00:00Z"
             */
            'published_at' => 'nullable|date',
        ];
    }
}
