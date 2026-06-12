<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates bulk media library file uploads.
 */
class BulkUploadMediaRequest extends BaseRequest
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
        $maxKb = (int) (config('media-library.max_file_size', 10 * 1024 * 1024) / 1024);

        return [
            /** @var list<\Illuminate\Http\UploadedFile> $files */
            'files' => 'required|array|min:1|max:50',
            'files.*' => "required|file|max:{$maxKb}",
            'folder_id' => 'nullable|integer|exists:media_library_folders,id',
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ];
    }
}
