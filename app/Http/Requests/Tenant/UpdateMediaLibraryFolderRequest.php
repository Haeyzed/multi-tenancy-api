<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for updating a media library folder.
 */
class UpdateMediaLibraryFolderRequest extends BaseRequest
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
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|integer|exists:media_library_folders,id',
        ];
    }
}
