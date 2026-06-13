<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Store;
use Illuminate\Validation\Rule;

/**
 * Validates incoming data for updating a store.
 */
class UpdateStoreRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Store|null $store */
        $store = $this->route('store');

        return [
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('stores', 'slug')->ignore($store?->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('stores', 'code')->ignore($store?->id),
            ],
            'type' => ['sometimes', 'string', Rule::in(StoreStoreRequest::typeOptions())],
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
            'is_primary' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ];
    }
}
