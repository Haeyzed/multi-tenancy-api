<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for creating a product category.
 */
class StoreCategoryRequest extends BaseRequest
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
             * Parent category ID for nested categories; nullable for root.
             *
             * @var string|null $parent_id
             */
            'parent_id' => 'nullable|uuid|exists:categories,id',

            /**
             * Display name of the category.
             *
             * @var string $name
             *
             * @example "Electronics"
             */
            'name' => 'required|string|max:255',

            /**
             * URL-friendly unique identifier for the category.
             *
             * @var string $slug
             *
             * @example "electronics"
             */
            'slug' => 'required|string|unique:categories,slug|max:255',

            /**
             * Marketing description of the category; nullable.
             *
             * @var string|null $description
             */
            'description' => 'nullable|string',

            /**
             * SEO meta title; nullable.
             *
             * @var string|null $meta_title
             */
            'meta_title' => 'nullable|string|max:255',

            /**
             * SEO meta description; nullable.
             *
             * @var string|null $meta_description
             */
            'meta_description' => 'nullable|string|max:500',

            /**
             * SEO meta keywords; nullable.
             *
             * @var string|null $meta_keywords
             */
            'meta_keywords' => 'nullable|string|max:255',

            /**
             * Banner media ID; nullable.
             *
             * @var int|null $banner_media_id
             */
            'banner_media_id' => 'nullable|integer|exists:media,id',

            /**
             * Icon media ID; nullable.
             *
             * @var int|null $icon_media_id
             */
            'icon_media_id' => 'nullable|integer|exists:media,id',

            /**
             * Display order in category lists; optional.
             *
             * @var int $sort_order
             */
            'sort_order' => 'sometimes|integer|min:0',

            /**
             * Whether the category is visible in the catalog; optional.
             *
             * @var bool $is_active
             */
            'is_active' => 'sometimes|boolean',

            /**
             * Whether the category is featured; optional.
             *
             * @var bool $is_featured
             */
            'is_featured' => 'sometimes|boolean',

            /**
             * Whether the category appears in navigation menus; optional.
             *
             * @var bool $show_in_menu
             */
            'show_in_menu' => 'sometimes|boolean',
        ];
    }
}
