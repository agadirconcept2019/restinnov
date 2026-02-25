<?php

namespace App\Core\Seo;

use App\Core\Settings\SettingsService;
use App\Models\CmsPages\Page;
use App\Models\Core\Media;
use App\Models\Core\SeoMeta;
use App\Models\RealEstate\Property;
use App\Modules\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class SeoManager
{
    public function __construct(
        private readonly BreadcrumbService $breadcrumbService,
        private readonly SettingsService $settingsService,
    ) {
    }

    public function resolveForRequest(Request $request): array
    {
        $path = $this->stripLocalePrefix('/'.ltrim($request->path(), '/'));
        $base = [
            'meta_title' => config('app.name'),
            'meta_description' => 'RestInnov CMS',
            'canonical_url' => $this->canonicalForPath($request->path()),
            'og_title' => null,
            'og_description' => null,
            'og_image' => null,
            'twitter_card' => 'summary_large_image',
            'robots' => 'index,follow',
            'schema_json' => [],
            'hreflang' => $this->hreflangAlternates($request->path()),
            'prev_url' => null,
            'next_url' => null,
            'breadcrumbs' => $this->breadcrumbService->build($request),
        ];

        ['metaable' => $metaable, 'fallback' => $fallback, 'context' => $context] = $this->resolveMetaable($request);
        $base['hreflang'] = $this->hreflangAlternatesFor($path, $metaable);

        $seo = $metaable ? $this->seoMetaFor($metaable) : null;

        $title = $seo?->meta_title ?: Arr::get($fallback, 'title', $base['meta_title']);
        $description = $seo?->meta_description ?: Arr::get($fallback, 'description', $base['meta_description']);

        $base['meta_title'] = $title;
        $base['meta_description'] = $description;
        $base['canonical_url'] = $this->absoluteCanonical($seo?->canonical_url ?: $base['canonical_url']);
        $base['og_title'] = $seo?->og_title ?: $title;
        $base['og_description'] = $seo?->og_description ?: $description;
        $base['og_image'] = $this->resolveOgImageUrl($metaable, $seo?->og_image_media_id);
        $base['robots'] = $seo?->robots ?: $base['robots'];
        $base['schema_json'] = $this->buildSchema($context, $metaable, $title, $description, $seo?->schema_json, $base['breadcrumbs']);
        ['prev' => $base['prev_url'], 'next' => $base['next_url']] = $this->paginationLinks($request, $path);

        return $base;
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

                return [
                    'metaable' => $page,
                    'fallback' => ['title' => $tr?->title, 'description' => $tr?->excerpt ?: (string) str($tr?->content)->limit(150)],
                    'context' => ['type' => $path === '/' ? 'home' : 'page'],
                ];
            }
        }

        if ($path === '/blog') {
            return [
                'metaable' => null,
                'fallback' => ['title' => 'Blog | '.config('app.name'), 'description' => 'Insights and updates from RestInnov'],
                'context' => ['type' => 'blog_index'],
            ];
        }

        if (preg_match('#^/blog/category/([^/]+)$#', $path, $matches)) {
            return [
                'metaable' => null,
                'fallback' => ['title' => str($matches[1])->replace('-', ' ')->title().' | Blog', 'description' => 'Blog category archive'],
                'context' => ['type' => 'blog_category', 'category_slug' => $matches[1]],
            ];
        }

        if (preg_match('#^/blog/([^/]+)$#', $path, $matches)) {
            $post = Post::query()->with(['translations', 'categories.translations'])->published()->where('slug', $matches[1])->first();
            if ($post) {
                $tr = $post->translated();

                return [
                    'metaable' => $post,
                    'fallback' => ['title' => $tr?->title, 'description' => $tr?->excerpt ?: (string) str($tr?->content)->limit(150)],
                    'context' => ['type' => 'blog_post'],
                ];
            }
        }

        if ($path === '/our-properties') {
            return [
                'metaable' => null,
                'fallback' => ['title' => 'Our Properties | '.config('app.name'), 'description' => 'Browse available rentals managed by RestInnov.'],
                'context' => ['type' => 'property_index'],
            ];
        }

        if (preg_match('#^/properties/([^/]+)$#', $path, $matches)) {
            $property = Property::query()->with(['translations', 'images.media', 'city.translations'])->published()->where('slug', $matches[1])->first();
            if ($property) {
                $tr = $property->translated();

                return [
                    'metaable' => $property,
                    'fallback' => ['title' => $tr?->title, 'description' => $tr?->excerpt ?: (string) str($tr?->description)->limit(150)],
                    'context' => ['type' => 'property_show'],
                ];
            }
        }

        return ['metaable' => null, 'fallback' => [], 'context' => ['type' => 'generic']];
    }

    private function seoMetaFor(object $metaable): ?SeoMeta
    {
        $locale = app()->getLocale();
        $defaultLocale = config('locales.default', 'en');

        return SeoMeta::query()
            ->where('metaable_type', $metaable::class)
            ->where('metaable_id', $metaable->getKey())
            ->whereIn('locale', [$locale, $defaultLocale, null])
            ->orderByRaw('case when locale = ? then 0 when locale = ? then 1 else 2 end', [$locale, $defaultLocale])
            ->first();
    }

    private function resolveOgImageUrl(?object $metaable, ?int $seoOgMediaId): ?string
    {
        $mediaId = $seoOgMediaId;

        if (! $mediaId && $metaable instanceof Property) {
            $cover = $metaable->images->firstWhere('is_cover', true) ?? $metaable->images->first();
            $mediaId = $cover?->media_id;
        }

        if (! $mediaId) {
            $mediaId = (int) ($this->settingsService->get('seo', 'default_og_image_media_id') ?: 0);
        }

        if ($mediaId <= 0) {
            return null;
        }

        $media = Media::query()->find($mediaId);

        return $media ? $this->mediaUrl($media) : null;
    }

    private function mediaUrl(Media $media): string
    {
        $relative = Storage::disk($media->disk)->url($media->path);

        if (str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://')) {
            return $relative;
        }

        return url($relative);
    }

    private function hreflangAlternatesFor(string $path, mixed $metaable): array
    {
        if (! $metaable || ! method_exists($metaable, 'translations')) {
            return $this->hreflangAlternates($path);
        }

        $default = config('locales.default', 'en');
        $availableLocales = $metaable->translations->pluck('locale')->filter()->unique()->values();

        if ($availableLocales->isEmpty()) {
            return $this->hreflangAlternates($path);
        }

        return $availableLocales->mapWithKeys(fn (string $locale) => [
            $locale => $locale === $default
                ? url($path === '/' ? '/' : $path)
                : url('/'.$locale.($path === '/' ? '' : $path)),
        ])->all();
    }

    private function buildSchema(array $context, mixed $metaable, string $title, string $description, mixed $customSchema, array $breadcrumbs): array
    {
        $schemas = [];
        $type = $context['type'] ?? 'generic';

        if ($type === 'home') {
            $logoId = (int) ($this->settingsService->get('seo', 'organization_logo_media_id') ?: 0);
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => config('app.name'),
                'url' => url('/'),
            ];
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => config('app.name'),
                'url' => url('/'),
                'logo' => $logoId > 0 && ($logo = Media::query()->find($logoId)) ? $this->mediaUrl($logo) : null,
            ];
        }

        if ($type === 'property_show' && $metaable instanceof Property) {
            $translation = $metaable->translated();
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'RealEstateListing',
                'name' => $translation?->title ?: $title,
                'description' => $translation?->excerpt ?: $description,
                'url' => url('/properties/'.$metaable->slug),
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $metaable->address_line,
                    'addressLocality' => $metaable->city?->translated()?->name,
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'priceCurrency' => $metaable->currency,
                    'price' => (float) $metaable->base_price_per_night,
                    'availability' => 'https://schema.org/InStock',
                ],
            ];
        }

        if ($type === 'blog_post' && $metaable instanceof Post) {
            $translation = $metaable->translated();
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $translation?->title ?: $title,
                'description' => $translation?->excerpt ?: $description,
                'datePublished' => optional($metaable->published_at)->toAtomString(),
                'dateModified' => optional($metaable->updated_at)->toAtomString(),
                'mainEntityOfPage' => $this->canonicalForPath('blog/'.$metaable->slug),
            ];
        }

        if ($type === 'page') {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'description' => $description,
                'url' => url()->current(),
            ];
        }

        if (count($breadcrumbs) > 1) {
            $schemas[] = $this->breadcrumbService->schema($breadcrumbs);
        }

        if (is_array($customSchema) && Arr::isAssoc($customSchema) && ! empty($customSchema)) {
            $schemas[] = $customSchema;
        } elseif (is_array($customSchema) && ! empty($customSchema)) {
            $schemas = [...$schemas, ...$customSchema];
        }

        return array_values(array_filter($schemas));
    }

    private function paginationLinks(Request $request, string $path): array
    {
        if (! in_array($path, ['/blog', '/our-properties'], true)) {
            return ['prev' => null, 'next' => null];
        }

        $currentPage = max(1, (int) $request->query('page', 1));
        $basePath = $path === '/' ? '/' : $path;
        $query = $request->query();

        $prev = null;
        if ($currentPage > 1) {
            $prevQuery = array_merge($query, ['page' => $currentPage - 1]);
            if (($currentPage - 1) === 1) {
                unset($prevQuery['page']);
            }
            $prev = url($basePath).(count($prevQuery) ? '?'.http_build_query($prevQuery) : '');
        }

        $nextQuery = array_merge($query, ['page' => $currentPage + 1]);
        $next = url($basePath).'?'.http_build_query($nextQuery);

        return ['prev' => $prev, 'next' => $next];
    }
}
