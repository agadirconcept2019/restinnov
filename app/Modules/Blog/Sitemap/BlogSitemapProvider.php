<?php

namespace App\Modules\Blog\Sitemap;

use App\Core\Sitemap\SitemapProviderInterface;
use App\Modules\Blog\Models\Post;

class BlogSitemapProvider implements SitemapProviderInterface
{
    public function items(): array
    {
        $items = [[
            'loc' => url('/blog'),
            'lastmod' => now()->toAtomString(),
            'alternates' => $this->alternates('/blog'),
        ]];

        foreach (Post::query()->published()->get() as $post) {
            $path = '/blog/'.$post->slug;
            $items[] = [
                'loc' => url($path),
                'lastmod' => optional($post->updated_at)->toAtomString(),
                'alternates' => $this->alternates($path),
            ];
        }

        return $items;
    }

    private function alternates(string $path): array
    {
        $default = config('locales.default', 'en');

        return collect(config('locales.supported', ['en', 'fr', 'es']))->mapWithKeys(fn ($locale) => [
            $locale => $locale === $default ? url($path) : url('/'.$locale.$path),
        ])->all();
    }
}
