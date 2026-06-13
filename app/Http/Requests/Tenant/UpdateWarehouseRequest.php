<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use App\Models\Tenant\Warehouse;
use Illuminate\Validation\Rule;

/**
 * Validates incoming data for updating a warehouse.
 */
class UpdateWarehouseRequest extends BaseRequest
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
        /** @var Warehouse|null $warehouse */
        $warehouse = $this->route('warehouse');

        return [
            'store_id' => 'nullable|integer|exists:stores,id',
            'name' => 'sometimes|string|max:255',
            'code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->ignore($warehouse?->id),
            ],
            'type' => ['sometimes', 'string', Rule::in(StoreWarehouseRequest::typeOptions())],
            'address' => 'nullable|array',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'timezone' => 'sometimes|string|max:64',
            'manager_id' => 'nullable|integer|exists:employees,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
