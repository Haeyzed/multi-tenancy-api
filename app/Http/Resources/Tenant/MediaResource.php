<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example 1 */
            'id' => $this->id,
            'folder_id' => $this->folder_id,
            /** @example "hero-banner" */
            'name' => $this->name,
            'title' => $this->title,
            'alt_text' => $this->alt_text,
            /** @example "hero-banner.jpg" */
            'file_name' => $this->file_name,
            /** @example "image/jpeg" */
            'mime_type' => $this->mime_type,
            /** @example "public" */
            'disk' => $this->disk,
            /** @example 204800 */
            'size' => $this->size,
            /** @example "https://store.example.com/storage/media/library/root/uuid.jpg" */
            'url' => $this->url,
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'folder' => new MediaLibraryFolderResource($this->whenLoaded('folder')),
            'uploader' => new UserResource($this->whenLoaded('uploader')),
        ];
    }
}
