<?php

namespace App\Providers;

use App\Core\Seo\SeoManager;
use App\Core\Sitemap\SitemapRegistry;
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
    }

    public function boot(SeoManager $seoManager): void
    {
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)->by($request->ip().'|'.strtolower((string) $request->input('email'))));
        RateLimiter::for('forms-public', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        View::composer('layouts.public', function ($view) use ($seoManager) {
            $view->with('seo', $seoManager->resolveForRequest(request()));
        });
    }
}
