<?php

namespace App\Modules\MigrationTools;

use Illuminate\Support\ServiceProvider;

class MigrationToolsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/MigrationTools');

        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'migrationtools');
    }
}
