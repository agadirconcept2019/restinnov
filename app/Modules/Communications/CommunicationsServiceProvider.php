<?php

namespace App\Modules\Communications;

use Illuminate\Support\ServiceProvider;

class CommunicationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/Communications');

        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'communications');
    }
}
