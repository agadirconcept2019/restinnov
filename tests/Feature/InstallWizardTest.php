<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_lock_blocks_install_routes(): void
    {
        File::put(storage_path('app/install.lock'), 'locked');

        $this->get('/install')->assertRedirect('/');

        File::delete(storage_path('app/install.lock'));
    }
}
