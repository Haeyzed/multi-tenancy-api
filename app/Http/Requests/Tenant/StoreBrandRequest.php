<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for creating a product brand.
 *
 * @property-read string $name Display name of the brand.
 * @property-read string $slug URL-friendly unique identifier for the brand.
 * @property-read string|null $description Marketing description of the brand.
 * @property-read int|null $logo_media_id Media library ID for the brand logo.
 * @property-read string|null $website_url Public website URL for the brand.
 * @property-read bool $is_active Whether the brand is visible in the catalog.
 * @property-read int $sort_order Display order in brand lists.
 */
class StoreBrandRequest extends BaseRequest
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:brands,slug|max:255',
            'description' => 'nullable|string',
            'logo_media_id' => 'nullable|integer|exists:media,id',
            'website_url' => 'nullable|url|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ];
    }
}
