<?php

namespace App\Modules\Blog;

use App\Core\Sitemap\SitemapRegistry;
use Illuminate\Support\ServiceProvider;
use App\Modules\Blog\Sitemap\BlogSitemapProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/Blog');

        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'blog');

        $this->app->make(SitemapRegistry::class)->register(new BlogSitemapProvider());
    }
}
