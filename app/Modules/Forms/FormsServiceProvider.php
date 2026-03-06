<?php

namespace App\Modules\Forms;

use Illuminate\Support\ServiceProvider;

class FormsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $base = base_path('modules/Forms');

        $this->loadRoutesFrom($base.'/routes/web.php');
        $this->loadRoutesFrom($base.'/routes/admin.php');
        $this->loadViewsFrom($base.'/resources/views', 'forms');
    }
}
