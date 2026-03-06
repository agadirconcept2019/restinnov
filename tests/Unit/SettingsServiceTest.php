<?php

namespace Tests\Unit;

use App\Core\Settings\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_service_stores_and_reads_values(): void
    {
        $service = app(SettingsService::class);
        $service->put('site', 'site_name', 'RestInnov');

        $this->assertSame('RestInnov', $service->get('site', 'site_name'));
    }
}
