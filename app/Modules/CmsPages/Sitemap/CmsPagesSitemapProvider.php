<?php

namespace App\Modules\CmsPages\Sitemap;

use App\Core\Sitemap\SitemapProviderInterface;
use App\Models\CmsPages\Page;

class CmsPagesSitemapProvider implements SitemapProviderInterface
{
    public function items(): array
    {
        return Page::query()->published()->get()->map(function (Page $page) {
            $path = $page->template === 'home' ? '/' : '/'.$page->slug;

            return [
                'loc' => url($path),
                'lastmod' => optional($page->updated_at)->toAtomString(),
                'alternates' => $this->alternates($path),
            ];
        })->all();
    }

    private function alternates(string $path): array
    {
        $default = config('locales.default', 'en');

        return collect(config('locales.supported', ['en', 'fr', 'es']))->mapWithKeys(fn ($locale) => [
            $locale => $locale === $default ? url($path) : url('/'.$locale.$path),
        ])->all();
    }
}
