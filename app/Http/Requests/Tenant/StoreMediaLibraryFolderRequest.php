<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates incoming data for creating a media library folder.
 */
class StoreMediaLibraryFolderRequest extends BaseRequest
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
            /** @var string $name @example "Product Images" */
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:media_library_folders,id',
        ];
    }
}
