<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::delete(storage_path('app/install.lock'));
        parent::tearDown();
    }

    public function test_install_route_accessible_before_lock(): void
    {
        File::delete(storage_path('app/install.lock'));

        $this->get('/install')->assertOk();
    }

    public function test_install_route_blocked_after_lock(): void
    {
        File::put(storage_path('app/install.lock'), 'locked');

        $this->get('/install')->assertRedirect(route('admin.login'));
    }

    public function test_db_config_validation_rejects_invalid_payload(): void
    {
        $this->post('/install/database', [
            'db_host' => '',
            'db_port' => 'invalid',
            'db_database' => '',
            'db_username' => '',
        ])->assertSessionHasErrors(['db_host', 'db_port', 'db_database', 'db_username']);
    }
}
