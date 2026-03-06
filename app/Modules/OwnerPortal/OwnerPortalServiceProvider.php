<?php

namespace App\Modules\OwnerPortal;

use Illuminate\Support\ServiceProvider;

class OwnerPortalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/OwnerPortal');
        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'ownerportal');
    }
}
