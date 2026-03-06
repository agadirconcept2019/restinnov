<?php

namespace App\Http\Controllers\Admin;

use App\Core\Modules\ModuleManager;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __invoke(ModuleManager $moduleManager)
    {
        return view('admin.dashboard', [
            'modulesCount' => $moduleManager->available()->count(),
            'enabledModulesCount' => $moduleManager->enabled()->count(),
            'installLocked' => file_exists(storage_path('app/install.lock')),
            'locale' => app()->getLocale(),
        ]);
    }
}
