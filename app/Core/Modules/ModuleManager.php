<?php

namespace App\Core\Modules;

use App\Models\Core\Module;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;

class ModuleManager
{
    public function __construct(private readonly ModuleManifestRepository $repository)
    {
    }

    public function available(): Collection
    {
        return $this->repository->all();
    }

    public function enabled(): Collection
    {
        $available = $this->available();

        if (! $this->hasModulesTable()) {
            return $available->only(config('modules.default_enabled', []));
        }

        $slugs = Module::query()
            ->where('is_enabled', true)
            ->pluck('slug')
            ->all();

        return $available->only($slugs);
    }

    public function syncRegistry(): void
    {
        if (! $this->hasModulesTable()) {
            return;
        }

        foreach ($this->available() as $manifest) {
            Module::query()->updateOrCreate(
                ['slug' => $manifest['slug']],
                [
                    'name' => $manifest['name'],
                    'version' => $manifest['version'],
                    'meta' => $manifest,
                ],
            );
        }
    }

    public function enable(array $slugs): void
    {
        if (! $this->hasModulesTable()) {
            return;
        }

        $available = $this->available();

        foreach ($slugs as $slug) {
            if (! $available->has($slug)) {
                continue;
            }

            Module::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $available[$slug]['name'],
                    'version' => $available[$slug]['version'],
                    'is_enabled' => true,
                    'installed_at' => now(),
                    'meta' => $available[$slug],
                ],
            );
        }
    }

    public function providersFromEnabledModules(): array
    {
        return $this->enabled()
            ->pluck('providers')
            ->flatten()
            ->filter(fn ($provider) => is_string($provider) && $provider !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function hasModulesTable(): bool
    {
        /** @var Builder $schema */
        $schema = app('db')->connection()->getSchemaBuilder();

        return $schema->hasTable('modules');
    }
}
