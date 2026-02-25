<?php

namespace App\Modules\MigrationTools\Services;

use Illuminate\Support\Facades\Http;

class WpRestImportService
{
    public function fetch(string $baseUrl, array $options = []): array
    {
        $client = Http::timeout(10);
        if (! empty($options['username']) && ! empty($options['password'])) {
            $client = $client->withBasicAuth($options['username'], $options['password']);
        }

        $pages = $client->get(rtrim($baseUrl, '/').'/wp-json/wp/v2/pages', ['per_page' => 100])->json() ?: [];
        $posts = $client->get(rtrim($baseUrl, '/').'/wp-json/wp/v2/posts', ['per_page' => 100])->json() ?: [];

        return [
            'pages' => collect($pages)->map(fn ($p) => [
                'source_id' => (string) ($p['id'] ?? ''),
                'slug' => $p['slug'] ?? '',
                'title' => $p['title']['rendered'] ?? '',
                'content' => $p['content']['rendered'] ?? '',
                'status' => ($p['status'] ?? 'publish') === 'publish' ? 'published' : 'draft',
                'link' => $p['link'] ?? '',
            ])->all(),
            'posts' => collect($posts)->map(fn ($p) => [
                'source_id' => (string) ($p['id'] ?? ''),
                'slug' => $p['slug'] ?? '',
                'title' => $p['title']['rendered'] ?? '',
                'content' => $p['content']['rendered'] ?? '',
                'status' => ($p['status'] ?? 'publish') === 'publish' ? 'published' : 'draft',
                'link' => $p['link'] ?? '',
            ])->all(),
        ];
    }
}
