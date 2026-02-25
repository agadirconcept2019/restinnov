<?php

namespace App\Core\Modules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ModuleManifestRepository
{
    public function all(): Collection
    {
        $paths = File::glob(base_path('modules/*/module.json')) ?: [];

        return collect($paths)
            ->map(fn (string $path): array => json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR))
            ->keyBy('slug');
    }
}
