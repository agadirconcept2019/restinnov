<?php

namespace App\Modules\RealEstate\Sitemap;

use App\Core\Sitemap\SitemapProviderInterface;
use App\Models\RealEstate\Property;

class RealEstateSitemapProvider implements SitemapProviderInterface
{
    public function items(): array
    {
        $items = [[
            'loc' => url('/our-properties'),
            'lastmod' => now()->toAtomString(),
            'alternates' => $this->alternates('/our-properties'),
        ]];

        foreach (Property::query()->published()->get() as $property) {
            $path = '/properties/'.$property->slug;
            $items[] = [
                'loc' => url($path),
                'lastmod' => optional($property->updated_at)->toAtomString(),
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
