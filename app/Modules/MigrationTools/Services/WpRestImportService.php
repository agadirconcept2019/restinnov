<?php

namespace App\Modules\MigrationTools\Services;

use App\Modules\MigrationTools\DTO\PageDTO;
use App\Modules\MigrationTools\DTO\PostDTO;
use App\Modules\MigrationTools\DTO\PropertyDTO;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class WpRestImportService
{
    public function fetch(string $baseUrl, array $options = []): array
    {
        $client = Http::timeout(12)->retry(3, 300, function ($exception, PendingRequest $request) {
            if (! method_exists($exception, 'response') || ! $exception->response) {
                return true;
            }

            return in_array($exception->response->status(), [429, 500, 502, 503, 504], true);
        });

        if (! empty($options['username']) && ! empty($options['password'])) {
            $client = $client->withBasicAuth($options['username'], $options['password']);
        }

        $modifiedSince = ! empty($options['modified_since']) ? Carbon::parse((string) $options['modified_since']) : null;
        $propertiesEndpoint = $options['properties_endpoint'] ?? '/wp-json/wp/v2/properties';

        return [
            'pages' => $this->fetchCollection($client, rtrim($baseUrl, '/').'/wp-json/wp/v2/pages', $modifiedSince),
            'posts' => $this->fetchCollection($client, rtrim($baseUrl, '/').'/wp-json/wp/v2/posts', $modifiedSince),
            'properties' => $this->fetchCollection($client, rtrim($baseUrl, '/').$propertiesEndpoint, $modifiedSince),
        ];
    }

    private function fetchCollection(PendingRequest $client, string $url, ?Carbon $modifiedSince): array
    {
        $page = 1;
        $all = [];

        while (true) {
            $response = $client->get($url, ['per_page' => 100, 'page' => $page]);
            if (! $response->successful()) {
                break;
            }

            $items = $response->json() ?: [];
            if (empty($items)) {
                break;
            }

            foreach ($items as $item) {
                if ($modifiedSince && ! empty($item['modified_gmt']) && Carbon::parse($item['modified_gmt'])->lte($modifiedSince)) {
                    continue;
                }

                $dto = str_contains($url, '/properties')
                    ? new PropertyDTO(
                        sourceId: (string) ($item['id'] ?? ''),
                        slug: (string) ($item['slug'] ?? ''),
                        title: (string) ($item['title']['rendered'] ?? ''),
                        status: ($item['status'] ?? 'publish') === 'publish' ? 'published' : 'draft',
                        basePricePerNight: (float) (($item['acf']['base_price_per_night'] ?? $item['meta']['base_price_per_night'] ?? 0)),
                        maxGuests: (int) (($item['acf']['max_guests'] ?? $item['meta']['max_guests'] ?? 1)),
                        description: (string) ($item['content']['rendered'] ?? ''),
                        modifiedGmt: $item['modified_gmt'] ?? null,
                        meta: ['meta' => $item['meta'] ?? [], 'acf' => $item['acf'] ?? []],
                    )
                    : (str_contains($url, '/posts')
                        ? new PostDTO(
                            sourceId: (string) ($item['id'] ?? ''),
                            slug: (string) ($item['slug'] ?? ''),
                            title: (string) ($item['title']['rendered'] ?? ''),
                            content: (string) ($item['content']['rendered'] ?? ''),
                            status: ($item['status'] ?? 'publish') === 'publish' ? 'published' : 'draft',
                            link: $item['link'] ?? null,
                            modifiedGmt: $item['modified_gmt'] ?? null,
                        )
                        : new PageDTO(
                            sourceId: (string) ($item['id'] ?? ''),
                            slug: (string) ($item['slug'] ?? ''),
                            title: (string) ($item['title']['rendered'] ?? ''),
                            content: (string) ($item['content']['rendered'] ?? ''),
                            status: ($item['status'] ?? 'publish') === 'publish' ? 'published' : 'draft',
                            link: $item['link'] ?? null,
                            modifiedGmt: $item['modified_gmt'] ?? null,
                        ));

                $all[] = $dto->toArray();
            }

            $totalPages = (int) $response->header('X-WP-TotalPages', 1);
            if ($page >= $totalPages) {
                break;
            }

            $page++;
        }

        return $all;
    }
}
