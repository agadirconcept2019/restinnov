<?php

namespace Tests\Unit;

use App\Core\Modules\ModuleManifestRepository;
use Tests\TestCase;

class ModuleManifestRepositoryTest extends TestCase
{
    public function test_module_manifest_repository_loads_valid_manifests(): void
    {
        $modules = app(ModuleManifestRepository::class)->all();

        $this->assertTrue($modules->has('cms-pages'));
        $this->assertTrue($modules->has('blog'));
        $this->assertTrue($modules->has('forms'));
    }
}
