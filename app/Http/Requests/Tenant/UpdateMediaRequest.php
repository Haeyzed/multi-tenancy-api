<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates media library metadata updates.
 */
class UpdateMediaRequest extends BaseRequest
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
            'title' => 'sometimes|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'folder_id' => 'nullable|integer|exists:media_library_folders,id',
        ];
    }
}
