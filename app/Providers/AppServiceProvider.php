<?php

namespace App\Providers;

use App\Core\Modules\ModuleManager;
use App\Core\Modules\ModuleManifestRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManifestRepository::class);
        $this->app->singleton(ModuleManager::class);
    }

    public function boot(): void
    {
        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(8)->by($request->ip()));
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
    }
}
