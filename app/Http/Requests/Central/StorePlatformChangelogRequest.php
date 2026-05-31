<?php

declare(strict_types=1);

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming data for creating a new platform changelog entry.
 */
class StorePlatformChangelogRequest extends FormRequest
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
             * Semantic version string for this release.
             *
             * @var string $version
             *
             * @example "2.4.0"
             */
            'version' => 'required|string|max:50',

            /**
             * Short title summarizing the release.
             *
             * @var string $title
             *
             * @example "Multi-currency support"
             */
            'title' => 'required|string|max:255',

            /**
             * Detailed description of changes in this release.
             *
             * @var string $description
             *
             * @example "Added support for EUR and GBP billing currencies."
             */
            'description' => 'required|string',

            /**
             * Category of change for filtering and display.
             *
             * @var string $type
             *
             * @example "feature"
             */
            'type' => 'required|string|in:feature,fix,breaking,security,performance',

            /**
             * Whether the entry is visible to tenants; optional.
             *
             * @var bool $is_published
             *
             * @example false
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
