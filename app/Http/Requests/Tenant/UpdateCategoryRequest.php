<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for updating a product category.
 *
 * @property-read int|null $parent_id Parent category ID for nested categories.
 * @property-read string|null $name Display name of the category.
 * @property-read string|null $slug URL-friendly unique identifier for the category.
 * @property-read string|null $description Marketing description of the category.
 * @property-read string|null $meta_title SEO meta title.
 * @property-read string|null $meta_description SEO meta description.
 * @property-read string|null $meta_keywords SEO meta keywords.
 * @property-read int|null $banner_media_id Banner media ID.
 * @property-read int|null $icon_media_id Icon media ID.
 * @property-read int|null $sort_order Display order in category lists.
 * @property-read bool|null $is_active Whether the category is visible in the catalog.
 * @property-read bool|null $is_featured Whether the category is featured.
 * @property-read bool|null $show_in_menu Whether the category appears in navigation menus.
 */
class UpdateCategoryRequest extends BaseRequest
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
        /** @var int $categoryId */
        $categoryId = $this->route('category');

        return [
            'parent_id' => 'nullable|integer|exists:categories,id|not_in:' . $categoryId,
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:categories,slug,' . $categoryId . '|max:255',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'banner_media_id' => 'nullable|integer|exists:media,id',
            'icon_media_id' => 'nullable|integer|exists:media,id',
            'sort_order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'show_in_menu' => 'sometimes|boolean',
        ];
    }
}
