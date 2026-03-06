<?php

namespace App\Modules\MigrationTools\DTO;

class PageDTO
{
    public function __construct(
        public readonly string $sourceId,
        public readonly string $slug,
        public readonly string $title,
        public readonly string $content,
        public readonly string $status,
        public readonly ?string $link = null,
        public readonly ?string $modifiedGmt = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'source_id' => $this->sourceId,
            'slug' => $this->slug,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'link' => $this->link,
            'modified_gmt' => $this->modifiedGmt,
        ];
    }
}
