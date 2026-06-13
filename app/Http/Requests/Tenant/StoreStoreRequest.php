<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;

/**
 * Validates incoming data for creating a store.
 */
class StoreStoreRequest extends BaseRequest
{
    /**
     * @return list<string>
     */
    public static function typeOptions(): array
    {
        return ['online', 'retail', 'popup', 'franchise', 'kiosk', 'hybrid'];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            /** @var string $name @example "Downtown Flagship" */
            'name' => 'required|string|max:255',
            /** @var string $slug @example "downtown-flagship" */
            'slug' => 'required|string|unique:stores,slug|max:255',
            /** @var string|null $code @example "DT-001" */
            'code' => 'nullable|string|unique:stores,code|max:50',
            'type' => ['sometimes', 'string', Rule::in(self::typeOptions())],
            'tagline' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:255',
            'address' => 'nullable|array',
            'timezone' => 'sometimes|string|max:64',
            'currency' => 'sometimes|string|size:3',
            'tax_rate' => 'sometimes|numeric|min:0|max:100',
            'opening_hours' => 'nullable|array',
            'manager_id' => 'nullable|integer|exists:employees,id',
            'logo_media_id' => 'nullable|integer|exists:media,id',
            'favicon_media_id' => 'nullable|integer|exists:media,id',
            /** @var bool $is_primary */
            'is_primary' => 'sometimes|boolean',
            /** @var bool $is_active */
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ];
    }
}
