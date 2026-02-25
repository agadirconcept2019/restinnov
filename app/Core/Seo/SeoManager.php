<?php

namespace App\Core\Seo;

use App\Models\CmsPages\Page;
use App\Models\Core\SeoMeta;
use App\Models\RealEstate\Property;
use App\Modules\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SeoManager
{
    public function resolveForRequest(Request $request): array
    {
        $base = [
            'meta_title' => config('app.name'),
            'meta_description' => 'RestInnov CMS',
            'canonical_url' => $this->canonicalForPath($request->path()),
            'og_title' => null,
            'og_description' => null,
            'og_image' => null,
            'twitter_card' => 'summary_large_image',
            'robots' => 'index,follow',
            'schema_json' => null,
            'hreflang' => $this->hreflangAlternates($request->path()),
        ];

        [$metaable, $fallback] = $this->resolveMetaable($request);
        if (! $metaable) {
            return $base;
        }

        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'en');

        $seo = SeoMeta::query()
            ->where('metaable_type', $metaable::class)
            ->where('metaable_id', $metaable->getKey())
            ->whereIn('locale', [$locale, $defaultLocale, null])
            ->orderByRaw('case when locale = ? then 0 when locale = ? then 1 else 2 end', [$locale, $defaultLocale])
            ->first();

        $title = $seo?->meta_title ?: Arr::get($fallback, 'title', $base['meta_title']);
        $description = $seo?->meta_description ?: Arr::get($fallback, 'description', $base['meta_description']);

        return [
            ...$base,
            'meta_title' => $title,
            'meta_description' => $description,
            'canonical_url' => $this->absoluteCanonical($seo?->canonical_url ?: $base['canonical_url']),
            'og_title' => $seo?->og_title ?: $title,
            'og_description' => $seo?->og_description ?: $description,
            'og_image' => null,
            'robots' => $seo?->robots ?: $base['robots'],
            'schema_json' => $seo?->schema_json,
        ];
    }

    public function absoluteCanonical(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    public function canonicalForPath(string $path): string
    {
        $normalized = $this->stripLocalePrefix('/'.ltrim($path, '/'));

        return url($normalized === '/' ? '/' : $normalized);
    }

    public function hreflangAlternates(string $path): array
    {
        $normalized = $this->stripLocalePrefix('/'.ltrim($path, '/'));
        $default = config('locales.default', 'en');

        return collect(config('locales.supported', ['en', 'fr', 'es']))
            ->mapWithKeys(fn (string $locale) => [
                $locale => $locale === $default
                    ? url($normalized === '/' ? '/' : $normalized)
                    : url('/'.$locale.($normalized === '/' ? '' : $normalized)),
            ])
            ->all();
    }

    private function stripLocalePrefix(string $path): string
    {
        $trimmed = '/'.trim($path, '/');
        $parts = explode('/', trim($trimmed, '/'));
        $supported = config('locales.supported', ['en', 'fr', 'es']);

        if (isset($parts[0]) && in_array($parts[0], $supported, true)) {
            array_shift($parts);
            return '/'.implode('/', $parts ?: []);
        }

        return $trimmed === '/'.'' ? '/' : $trimmed;
    }

    private function resolveMetaable(Request $request): array
    {
        $path = $this->stripLocalePrefix('/'.ltrim($request->path(), '/'));

        if ($path === '/' || in_array($path, ['/our-services', '/rd', '/faq', '/contact-us', '/terms-and-conditions'], true)) {
            $slug = $path === '/' ? null : ltrim($path, '/');
            $query = Page::query()->with('translations')->published();
            $page = $slug ? $query->where('slug', $slug)->first() : $query->where('template', 'home')->first();
            if ($page) {
                $tr = $page->translated();
                return [$page, ['title' => $tr?->title, 'description' => $tr?->excerpt ?: (string) str($tr?->content)->limit(150)]];
            }
        }

        if ($path === '/blog') {
            return [null, ['title' => 'Blog | '.config('app.name'), 'description' => 'Insights and updates from RestInnov']];
        }

        if (preg_match('#^/blog/([^/]+)$#', $path, $matches)) {
            $post = Post::query()->with('translations')->published()->where('slug', $matches[1])->first();
            if ($post) {
                $tr = $post->translated();
                return [$post, ['title' => $tr?->title, 'description' => $tr?->excerpt ?: (string) str($tr?->content)->limit(150)]];
            }
        }

        if ($path === '/our-properties') {
            return [null, ['title' => 'Our Properties | '.config('app.name'), 'description' => 'Browse available rentals managed by RestInnov.']];
        }

        if (preg_match('#^/properties/([^/]+)$#', $path, $matches)) {
            $property = Property::query()->with('translations')->published()->where('slug', $matches[1])->first();
            if ($property) {
                $tr = $property->translated();
                return [$property, ['title' => $tr?->title, 'description' => $tr?->excerpt ?: (string) str($tr?->description)->limit(150)]];
            }
        }

        return [null, []];
    }
}
