<?php

namespace App\Modules\RealEstate;

use Illuminate\Support\ServiceProvider;

class RealEstateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/RealEstate');

        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'realestate');
    }
}
