<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray($request): array
    {
        $tr = $this->translated();

        return [
            'slug' => $this->slug,
            'status' => $this->status,
            'title' => $tr?->title,
            'excerpt' => $tr?->excerpt,
            'description' => $tr?->description,
            'city' => $this->city?->translated()?->name,
            'type' => $this->type?->translated()?->name,
            'rental_mode' => $this->mode?->translated()?->name,
            'max_guests' => $this->max_guests,
            'base_price_per_night' => (float) $this->base_price_per_night,
            'currency' => $this->currency,
            'is_featured' => (bool) $this->is_featured,
            'amenities' => $this->whenLoaded('amenities', fn () => $this->amenities->map(fn ($a) => $a->translated()?->name)->filter()->values()),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($i) => ['path' => $i->path, 'alt' => $i->alt_text])->values()),
        ];
    }
}
