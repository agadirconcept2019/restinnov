<?php

namespace App\Core\Modules;

use App\Models\Core\Module;
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

    public function active(): Collection
    {
        $activeSlugs = Module::query()->where('is_active', true)->pluck('slug')->all();

        return $this->available()->only($activeSlugs);
    }

    public function activate(array $slugs): void
    {
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
                    'is_active' => true,
                    'metadata' => $available[$slug],
                ],
            );
        }
    }
}
