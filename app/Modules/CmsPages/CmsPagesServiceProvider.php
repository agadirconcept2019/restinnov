<?php

namespace App\Modules\CmsPages;

use App\Core\Sitemap\SitemapRegistry;
use Illuminate\Support\ServiceProvider;
use App\Modules\CmsPages\Sitemap\CmsPagesSitemapProvider;

class CmsPagesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/CmsPages');

        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'cmspages');

        $this->app->make(SitemapRegistry::class)->register(new CmsPagesSitemapProvider());
    }
}
