<?php

namespace App\Core\Seo;

use App\Models\CmsPages\Page;
use App\Models\RealEstate\Property;
use App\Modules\Blog\Models\Post;
use Illuminate\Http\Request;

class BreadcrumbService
{
    public function build(Request $request): array
    {
        $path = '/'.trim($request->path(), '/');
        $path = $this->stripLocalePrefix($path);

        $items = [['label' => 'Home', 'url' => route('cms.home')]];

        if ($path === '/our-properties') {
            $items[] = ['label' => 'Our Properties', 'url' => url('/our-properties')];

            return $items;
        }

        if (preg_match('#^/properties/([^/]+)$#', $path, $m)) {
            $property = Property::query()->with(['translations', 'city.translations'])->published()->where('slug', $m[1])->first();
            $items[] = ['label' => 'Our Properties', 'url' => url('/our-properties')];
            if ($property?->city?->translated()?->name) {
                $items[] = ['label' => $property->city->translated()?->name, 'url' => url('/city/'.$property->city->slug)];
            }
            if ($property?->translated()?->title) {
                $items[] = ['label' => $property->translated()?->title, 'url' => url($path)];
            }

            return $items;
        }

        if ($path === '/blog') {
            $items[] = ['label' => 'Blog', 'url' => url('/blog')];

            return $items;
        }

        if (preg_match('#^/blog/category/([^/]+)$#', $path, $m)) {
            $items[] = ['label' => 'Blog', 'url' => url('/blog')];
            $items[] = ['label' => str($m[1])->replace('-', ' ')->title()->toString(), 'url' => url($path)];

            return $items;
        }

        if (preg_match('#^/blog/([^/]+)$#', $path, $m)) {
            $post = Post::query()->with('translations')->published()->where('slug', $m[1])->first();
            $items[] = ['label' => 'Blog', 'url' => url('/blog')];
            if ($post?->translated()?->title) {
                $items[] = ['label' => $post->translated()?->title, 'url' => url($path)];
            }

            return $items;
        }

        if ($path !== '/') {
            $page = Page::query()->with('translations')->published()->where('slug', ltrim($path, '/'))->first();
            if ($page?->translated()?->title) {
                $items[] = ['label' => $page->translated()?->title, 'url' => url($path)];
            }
        }

        return $items;
    }

    public function schema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => $item['url'],
            ])->all(),
        ];
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
}
