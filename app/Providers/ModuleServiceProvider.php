<?php

namespace App\Providers;

use App\Core\Modules\ModuleManager;
use App\Core\Modules\ModuleManifestRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleManifestRepository::class);
        $this->app->singleton(ModuleManager::class);

        if (! config('modules.autoload_providers', true)) {
            return;
        }

        try {
            /** @var ModuleManager $moduleManager */
            $moduleManager = $this->app->make(ModuleManager::class);

            foreach ($moduleManager->providersFromEnabledModules() as $provider) {
                if (! class_exists($provider)) {
                    logger()->warning('Module provider class missing', ['provider' => $provider]);

                    continue;
                }

                $this->app->register($provider);
            }
        } catch (\Throwable $e) {
            logger()->warning('Module provider auto-loading skipped', ['error' => $e->getMessage()]);
        }
    }
}
