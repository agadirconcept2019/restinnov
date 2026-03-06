<?php

namespace App\Providers;

use App\Core\Cache\CacheVersionManager;
use App\Core\Lock\LockService;
use App\Core\Seo\SeoManager;
use App\Core\Sitemap\SitemapRegistry;
use App\Models\CmsPages\Page;
use App\Models\Core\Redirect;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyAvailability;
use App\Modules\Blog\Models\Post;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SitemapRegistry::class);
        $this->app->singleton(LockService::class);
        $this->app->singleton(CacheVersionManager::class);
    }

    public function boot(SeoManager $seoManager, CacheVersionManager $cacheVersionManager, SitemapRegistry $sitemapRegistry): void
    {
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)->by($request->ip().'|'.strtolower((string) $request->input('email'))));
        RateLimiter::for('forms-public', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('api-auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip().'|'.strtolower((string) $request->input('email'))));
        RateLimiter::for('api-public', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('api-private', fn (Request $request) => Limit::perMinute(90)->by(($request->user()?->id ?: 'guest').'|'.$request->ip()));

        View::composer('layouts.public', function ($view) use ($seoManager) {
            $view->with('seo', $seoManager->resolveForRequest(request()));
        });

        $flushSitemap = function () use ($sitemapRegistry) {
            $sitemapRegistry->flush();
        };

        $bumpRealEstateCaches = function () use ($cacheVersionManager) {
            $cacheVersionManager->bump('realestate.search');
            $cacheVersionManager->bump('realestate.featured');
        };

        Page::saved($flushSitemap);
        Page::deleted($flushSitemap);
        Post::saved($flushSitemap);
        Post::deleted($flushSitemap);
        Redirect::saved($flushSitemap);
        Redirect::deleted($flushSitemap);

        Property::saved(function () use ($flushSitemap, $bumpRealEstateCaches) {
            $flushSitemap();
            $bumpRealEstateCaches();
        });
        Property::deleted(function () use ($flushSitemap, $bumpRealEstateCaches) {
            $flushSitemap();
            $bumpRealEstateCaches();
        });

        PropertyAvailability::saved($bumpRealEstateCaches);
        PropertyAvailability::deleted($bumpRealEstateCaches);
    }
}
