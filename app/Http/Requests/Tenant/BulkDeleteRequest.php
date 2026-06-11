<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Base validation for bulk delete payloads.
 */
abstract class BulkDeleteRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => $this->idRule(),
        ];
    }

    abstract protected function idRule(): string;
}
