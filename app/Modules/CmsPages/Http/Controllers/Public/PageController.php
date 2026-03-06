<?php

namespace App\Modules\CmsPages\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPages\Page;
use App\Models\RealEstate\Property;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::query()->with('translations')->published()->where('template', 'home')->firstOrFail();

        return $this->renderPage($page, true);
    }

    public function services() { return $this->renderBySlug('our-services'); }
    public function rd() { return $this->renderBySlug('rd'); }
    public function faq() { return $this->renderBySlug('faq'); }
    public function contact() { return $this->renderBySlug('contact-us'); }
    public function terms() { return $this->renderBySlug('terms-and-conditions'); }

    private function renderBySlug(string $slug)
    {
        $page = Page::query()->with('translations')->published()->where('slug', $slug)->firstOrFail();

        return $this->renderPage($page);
    }

    private function renderPage(Page $page, bool $withFeatured = false)
    {
        $translation = $page->translated();
        $featuredProperties = collect();

        if ($withFeatured) {
            $count = (int) data_get($translation?->template_data, 'featured_properties.count', 6);
            $featuredProperties = Cache::remember('home:featured_properties:'.$count, now()->addMinutes(10), function () use ($count) {
                return Property::query()
                    ->with(['translations', 'city.translations'])
                    ->published()
                    ->where('is_featured', true)
                    ->take(max(1, min(12, $count)))
                    ->get();
            });
        }

        return view('cmspages::public.page', compact('page', 'translation', 'featuredProperties'));
    }
}
