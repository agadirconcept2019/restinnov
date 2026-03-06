<?php

namespace App\Core\Modules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ModuleManifestRepository
{
    public function all(): Collection
    {
        $paths = File::glob(config('modules.path').'/*/module.json') ?: [];

        return collect($paths)
            ->map(fn (string $path) => $this->parseManifest($path))
            ->filter()
            ->keyBy('slug');
    }

    private function parseManifest(string $path): ?array
    {
        try {
            $data = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            logger()->channel('single')->warning('Invalid module manifest JSON', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }

        foreach (['name', 'slug', 'version', 'providers'] as $field) {
            if (! array_key_exists($field, $data)) {
                logger()->channel('single')->warning('Module manifest missing required field', ['path' => $path, 'field' => $field]);

                return null;
            }
        }

        $data['providers'] = is_array($data['providers']) ? $data['providers'] : [];

        return $data;
    }
}
