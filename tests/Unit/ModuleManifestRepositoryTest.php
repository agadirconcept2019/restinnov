<?php

namespace Tests\Unit;

use App\Core\Modules\ModuleManifestRepository;
use Tests\TestCase;

class ModuleManifestRepositoryTest extends TestCase
{
    public function test_it_loads_module_manifests(): void
    {
        $modules = app(ModuleManifestRepository::class)->all();

        $this->assertTrue($modules->has('real-estate'));
        $this->assertTrue($modules->has('cms-pages'));
    }
}
