<?php

namespace Tests\Unit;

use App\Core\Settings\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_from_cacheable_settings_store(): void
    {
        $service = app(SettingsService::class);
        $service->put('site_name', 'Rest Innov');

        $this->assertSame('Rest Innov', $service->get('site_name'));
    }
}
