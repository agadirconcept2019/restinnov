<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        $tr = $this->translated();

        return [
            'slug' => $this->slug,
            'title' => $tr?->title,
            'excerpt' => $tr?->excerpt,
            'content' => $tr?->content,
            'published_at' => optional($this->published_at)?->toIso8601String(),
            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($c) => $c->translated()?->name)->filter()->values()),
        ];
    }
}
