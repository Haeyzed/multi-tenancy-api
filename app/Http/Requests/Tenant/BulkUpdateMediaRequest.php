<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk metadata updates for media library files.
 */
class BulkUpdateMediaRequest extends BaseRequest
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
            'ids.*' => 'integer|exists:media,id',
            'title' => 'required_without:alt_text|nullable|string|max:255',
            'alt_text' => 'required_without:title|nullable|string|max:255',
        ];
    }
}
