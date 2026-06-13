<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Validation\Rule;

/**
 * Validates incoming data for creating a warehouse.
 */
class StoreWarehouseRequest extends BaseRequest
{
    /**
     * @return list<string>
     */
    public static function typeOptions(): array
    {
        return ['main', 'retail', 'return', 'dropship', 'fulfillment'];
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
            'store_id' => 'nullable|integer|exists:stores,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:warehouses,code|max:50',
            'type' => ['required', 'string', Rule::in(self::typeOptions())],
            'address' => 'nullable|array',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'timezone' => 'sometimes|string|max:64',
            'manager_id' => 'nullable|integer|exists:employees,id',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
