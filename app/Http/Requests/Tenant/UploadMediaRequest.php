<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

/**
 * Validates media library file upload.
 */
class UploadMediaRequest extends BaseRequest
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
            /** @var \Illuminate\Http\UploadedFile $file */
            'file' => "required|file|max:{$maxKb}",
            'folder_id' => 'nullable|integer|exists:media_library_folders,id',
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ];
    }
}
