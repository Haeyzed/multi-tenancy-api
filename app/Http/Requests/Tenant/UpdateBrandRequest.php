<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Brand;
use Illuminate\Validation\Rule;

/**
 * Validates incoming data for updating a product brand.
 */
class UpdateBrandRequest extends BaseRequest
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
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Brand|null $brand */
        $brand = $this->route('brand');

        return [
            /**
             * Display name of the brand; optional on update.
             *
             * @var string $name
             *
             * @example "Acme"
             */
            'name' => 'sometimes|string|max:255',

            /**
             * URL-friendly unique identifier for the brand; optional on update.
             *
             * @var string $slug
             *
             * @example "acme"
             */
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')->ignore($brand?->id),
            ],

            /**
             * Marketing description of the brand; nullable.
             *
             * @var string|null $description
             */
            'description' => 'nullable|string',

            /**
             * Media library ID for the brand logo; nullable.
             *
             * @var int|null $logo_media_id
             */
            'logo_media_id' => 'nullable|integer|exists:media,id',

            /**
             * Public website URL for the brand; nullable.
             *
             * @var string|null $website_url
             */
            'website_url' => 'nullable|url|max:255',

            /**
             * Whether the brand is visible in the catalog; optional.
             *
             * @var bool $is_active
             */
            'is_active' => 'sometimes|boolean',

            /**
             * Display order in brand lists; optional.
             *
             * @var int $sort_order
             */
            'sort_order' => 'sometimes|integer|min:0',
        ];
    }
}
