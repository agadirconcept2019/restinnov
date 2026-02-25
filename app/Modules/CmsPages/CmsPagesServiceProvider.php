<?php

namespace App\Modules\CmsPages;

use Illuminate\Support\ServiceProvider;

class CmsPagesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/CmsPages');

        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'cmspages');
    }
}
