<?php

namespace App\Modules\MigrationTools\DTO;

class PropertyDTO
{
    public function __construct(
        public readonly string $sourceId,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $status,
        public readonly float $basePricePerNight,
        public readonly int $maxGuests,
        public readonly ?string $description = null,
        public readonly ?string $modifiedGmt = null,
        public readonly array $meta = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'source_id' => $this->sourceId,
            'slug' => $this->slug,
            'title' => $this->title,
            'status' => $this->status,
            'base_price_per_night' => $this->basePricePerNight,
            'max_guests' => $this->maxGuests,
            'description' => $this->description,
            'modified_gmt' => $this->modifiedGmt,
            'meta' => $this->meta,
        ];
    }
}
