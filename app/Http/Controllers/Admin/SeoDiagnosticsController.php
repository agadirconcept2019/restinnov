<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPages\Page;
use App\Models\Core\Redirect;
use App\Models\Core\SeoMeta;
use App\Models\RealEstate\Property;
use App\Modules\Blog\Models\Post;
use Illuminate\Support\Facades\Cache;

class SeoDiagnosticsController extends Controller
{
    public function __invoke()
    {
        $supportedLocales = config('locales.supported', ['en', 'fr', 'es']);

        $data = [
            'missing_meta_titles' => [
                'pages' => $this->missingMetaTitles(Page::class, Page::query()->published()->pluck('id')->all()),
                'posts' => $this->missingMetaTitles(Post::class, Post::query()->published()->pluck('id')->all()),
                'properties' => $this->missingMetaTitles(Property::class, Property::query()->published()->pluck('id')->all()),
            ],
            'missing_hreflang' => [
                'pages' => Page::query()->with('translations')->published()->get()->filter(fn (Page $page) => $this->hasMissingTranslations($page, $supportedLocales)),
                'posts' => Post::query()->with('translations')->published()->get()->filter(fn (Post $post) => $this->hasMissingTranslations($post, $supportedLocales)),
                'properties' => Property::query()->with('translations')->published()->get()->filter(fn (Property $property) => $this->hasMissingTranslations($property, $supportedLocales)),
            ],
            'redirect_loops' => $this->redirectLoops(),
            'sitemap_cache' => [
                'is_cached' => Cache::has('sitemap:xml'),
                'cache_key' => 'sitemap:xml',
            ],
        ];

        return view('admin.seo.diagnostics', $data);
    }

    private function missingMetaTitles(string $metaableType, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $coveredIds = SeoMeta::query()
            ->where('metaable_type', $metaableType)
            ->whereIn('metaable_id', $ids)
            ->whereNotNull('meta_title')
            ->where('meta_title', '!=', '')
            ->pluck('metaable_id')
            ->unique()
            ->all();

        return array_values(array_diff($ids, $coveredIds));
    }

    private function hasMissingTranslations(object $model, array $supportedLocales): bool
    {
        $available = $model->translations->pluck('locale')->filter()->unique()->all();

        foreach ($supportedLocales as $locale) {
            if (! in_array($locale, $available, true)) {
                return true;
            }
        }

        return false;
    }

    private function redirectLoops(): array
    {
        $redirects = Redirect::query()->where('is_active', true)->get()->keyBy('from_path');
        $loops = [];

        foreach ($redirects as $from => $redirect) {
            $visited = [];
            $current = $from;

            while (isset($redirects[$current])) {
                if (in_array($current, $visited, true)) {
                    $visited[] = $current;
                    $loops[] = $visited;
                    break;
                }

                $visited[] = $current;
                $nextPath = parse_url($redirects[$current]->to_url, PHP_URL_PATH) ?: '';
                $current = '/'.ltrim($nextPath, '/');
            }
        }

        return collect($loops)->unique(fn (array $loop) => implode('>', $loop))->values()->all();
    }
}
