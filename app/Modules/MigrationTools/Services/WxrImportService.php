<?php

namespace App\Modules\MigrationTools\Services;

class WxrImportService
{
    public function parse(string $xml): array
    {
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (! $doc) {
            return ['pages' => [], 'posts' => []];
        }

        $items = $doc->channel->item ?? [];
        $pages = [];
        $posts = [];
        foreach ($items as $item) {
            $type = (string) ($item->children('wp', true)->post_type ?? '');
            $status = (string) ($item->children('wp', true)->status ?? 'publish');
            $entry = [
                'source_id' => (string) ($item->children('wp', true)->post_id ?? ''),
                'slug' => (string) ($item->children('wp', true)->post_name ?? str($item->title)->slug()),
                'title' => (string) $item->title,
                'content' => (string) ($item->children('content', true)->encoded ?? ''),
                'status' => $status === 'publish' ? 'published' : 'draft',
                'link' => (string) ($item->link ?? ''),
            ];

            if ($type === 'page') {
                $pages[] = $entry;
            }
            if ($type === 'post') {
                $posts[] = $entry;
            }
        }

        return ['pages' => $pages, 'posts' => $posts];
    }
}
