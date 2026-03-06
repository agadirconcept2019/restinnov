<?php

namespace App\Core\Media;

use App\Models\Core\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaVariantService
{
    public function createFromUpload(UploadedFile $file, ?int $userId = null): Media
    {
        $path = $file->store('media', 'public');
        [$width, $height] = $this->dimensions($file->getRealPath());

        $media = Media::query()->create([
            'disk' => 'public',
            'path' => $path,
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $userId,
            'meta' => ['variants' => $this->buildVariants($path)],
        ]);

        return $media;
    }

    private function buildVariants(string $path): array
    {
        $disk = Storage::disk('public');

        return [
            'thumb' => $disk->exists($path) ? $disk->url($path) : null,
            'medium' => $disk->exists($path) ? $disk->url($path) : null,
            'large' => $disk->exists($path) ? $disk->url($path) : null,
        ];
    }

    private function dimensions(string $realPath): array
    {
        $size = @getimagesize($realPath);

        return [$size[0] ?? null, $size[1] ?? null];
    }
}
