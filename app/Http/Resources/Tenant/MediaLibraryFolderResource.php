<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\MediaLibraryFolder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MediaLibraryFolder
 */
class MediaLibraryFolderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            /** @example "Product Images" */
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            /** @example "Product Images" */
            'path' => $this->path,
            'media_count' => $this->when(isset($this->media_count), $this->media_count),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
