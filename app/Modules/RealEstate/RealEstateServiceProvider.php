<?php

namespace App\Modules\RealEstate;

use App\Core\Sitemap\SitemapRegistry;
use Illuminate\Support\ServiceProvider;
use App\Modules\RealEstate\Sitemap\RealEstateSitemapProvider;

class RealEstateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/RealEstate');

        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'realestate');

        $this->app->make(SitemapRegistry::class)->register(new RealEstateSitemapProvider());
    }
}
