<?php

namespace App\Core\Sitemap;

use Illuminate\Support\Facades\Cache;

class SitemapRegistry
{
    /** @var array<int, SitemapProviderInterface> */
    private array $providers = [];

    public function register(SitemapProviderInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    public function renderXml(): string
    {
        return Cache::remember('sitemap:xml', now()->addMinutes(30), function () {
            $items = collect($this->providers)
                ->flatMap(fn (SitemapProviderInterface $provider) => $provider->items())
                ->unique('loc')
                ->values();

            return view('sitemap', ['items' => $items])->render();
        });
    }

    public function flush(): void
    {
        Cache::forget('sitemap:xml');
    }
}
