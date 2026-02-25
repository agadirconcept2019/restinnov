<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        $tr = $this->translated();

        return [
            'slug' => $this->slug,
            'template' => $this->template,
            'title' => $tr?->title,
            'content' => $tr?->content,
            'excerpt' => $tr?->excerpt,
            'published_at' => optional($this->published_at)?->toIso8601String(),
        ];
    }
}
