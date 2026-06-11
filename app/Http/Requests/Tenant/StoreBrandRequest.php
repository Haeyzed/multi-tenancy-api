<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for creating a product brand.
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
            /**
             * Display name of the brand.
             *
             * @var string $name
             *
             * @example "Acme"
             */
            'name' => 'required|string|max:255',

            /**
             * URL-friendly unique identifier for the brand.
             *
             * @var string $slug
             *
             * @example "acme"
             */
            'slug' => 'required|string|unique:brands,slug|max:255',

            /**
             * Marketing description of the brand; nullable.
             *
             * @var string|null $description
             *
             * @example "Premium outdoor gear"
             */
            'description' => 'nullable|string',

            /**
             * Media library ID for the brand logo; nullable.
             *
             * @var int|null $logo_media_id
             *
             * @example 12
             */
            'logo_media_id' => 'nullable|integer|exists:media,id',

            /**
             * Public website URL for the brand; nullable.
             *
             * @var string|null $website_url
             *
             * @example "https://acme.example.com"
             */
            'website_url' => 'nullable|url|max:255',

            /**
             * Whether the brand is visible in the catalog; optional.
             *
             * @var bool $is_active
             *
             * @example true
             */
            'is_active' => 'sometimes|boolean',

            /**
             * Display order in brand lists; optional.
             *
             * @var int $sort_order
             *
             * @example 1
             */
            'sort_order' => 'sometimes|integer|min:0',
        ];
    }
}
