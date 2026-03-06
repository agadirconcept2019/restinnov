<?php

namespace Tests\Feature;

use App\Models\RealEstate\Booking;
use App\Models\RealEstate\BookingRequest;
use App\Models\User;
use App\Modules\MigrationTools\Models\MigrationProfile;
use App\Modules\MigrationTools\Models\MigrationRun;
use App\Modules\MigrationTools\Services\MigrationManager;
use App\Modules\MigrationTools\Services\RemoteMediaDownloader;
use App\Modules\MigrationTools\Services\WpRestImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase10MigrationReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_delta_run_imports_only_changed_items(): void
    {
        $this->seed();
        $profile = MigrationProfile::query()->create([
            'name' => 'WP Delta',
            'source_type' => 'wp_rest',
            'base_url' => 'https://wp.example',
            'delta_strategy' => 'since_last_run',
            'overwrite_strategy' => 'update_if_exists',
            'endpoints' => ['properties' => '/wp-json/wp/v2/properties'],
        ]);

        Http::fake([
            'https://wp.example/wp-json/wp/v2/pages*' => Http::response([
                ['id' => 1, 'slug' => 'about', 'title' => ['rendered' => 'About'], 'content' => ['rendered' => '<p>a</p>'], 'status' => 'publish', 'modified_gmt' => now()->subDay()->toIso8601String()],
            ], 200, ['X-WP-TotalPages' => 1]),
            'https://wp.example/wp-json/wp/v2/posts*' => Http::response([], 200, ['X-WP-TotalPages' => 1]),
            'https://wp.example/wp-json/wp/v2/properties*' => Http::response([], 200, ['X-WP-TotalPages' => 1]),
        ]);

        $run1 = MigrationRun::query()->create(['profile_id' => $profile->id, 'source_type' => 'wp_rest', 'status' => 'queued', 'options' => ['source_type' => 'wp_rest', 'base_url' => 'https://wp.example', 'delta_strategy' => 'since_last_run', 'overwrite_strategy' => 'update_if_exists']]);
        app(MigrationManager::class)->processRun($run1);

        $run2 = MigrationRun::query()->create(['profile_id' => $profile->id, 'source_type' => 'wp_rest', 'status' => 'queued', 'options' => ['source_type' => 'wp_rest', 'base_url' => 'https://wp.example', 'delta_strategy' => 'since_last_run', 'overwrite_strategy' => 'update_if_exists']]);
        app(MigrationManager::class)->processRun($run2);

        $this->assertSame(0, (int) (($run2->fresh()->summary['created'] ?? 0)));
    }

    public function test_rollback_run_deletes_only_created_entities(): void
    {
        $this->seed();
        $xml = File::get(base_path('tests/Fixtures/sample-wp-export.xml'));
        $run = MigrationRun::query()->create(['source_type' => 'wxr_xml', 'status' => 'queued', 'options' => ['source_type' => 'wxr_xml', 'wxr_content' => $xml, 'overwrite_strategy' => 'update_if_exists']]);
        $manager = app(MigrationManager::class);
        $manager->processRun($run);

        $result = $manager->rollbackRun($run->fresh());
        $this->assertGreaterThanOrEqual(1, $result['deleted']);
        $this->assertDatabaseMissing('pages', ['slug' => 'about-restinnov']);
    }

    public function test_rollback_item_deletes_only_created_item(): void
    {
        $this->seed();
        $xml = File::get(base_path('tests/Fixtures/sample-wp-export.xml'));
        $run = MigrationRun::query()->create(['source_type' => 'wxr_xml', 'status' => 'queued', 'options' => ['source_type' => 'wxr_xml', 'wxr_content' => $xml, 'overwrite_strategy' => 'update_if_exists']]);
        $manager = app(MigrationManager::class);
        $manager->processRun($run);

        $item = $run->fresh()->items()->where('entity_type', 'page')->firstOrFail();
        $this->assertTrue($manager->rollbackItem($item));
    }

    public function test_csv_mapping_validation_rejects_missing_required_mapping(): void
    {
        $this->seed();
        $admin = User::factory()->create();
        $csvPath = storage_path('app/test-phase10.csv');
        file_put_contents($csvPath, "id,title\n1,One");

        $this->actingAs($admin)
            ->post(route('admin.migration.wizard.csv-mapping'), [
                'csv_file' => new \Illuminate\Http\UploadedFile($csvPath, 'test-phase10.csv', null, null, true),
                'mapping' => ['title' => 'title'],
            ])
            ->assertSessionHasErrors('mapping');
    }

    public function test_wp_rest_adapter_pagination_works(): void
    {
        Http::fake([
            'https://wp.example/wp-json/wp/v2/pages*page=1*' => Http::response([
                ['id' => 1, 'slug' => 'a', 'title' => ['rendered' => 'A'], 'content' => ['rendered' => 'A'], 'status' => 'publish'],
            ], 200, ['X-WP-TotalPages' => 2]),
            'https://wp.example/wp-json/wp/v2/pages*page=2*' => Http::response([
                ['id' => 2, 'slug' => 'b', 'title' => ['rendered' => 'B'], 'content' => ['rendered' => 'B'], 'status' => 'publish'],
            ], 200, ['X-WP-TotalPages' => 2]),
            'https://wp.example/wp-json/wp/v2/posts*' => Http::response([], 200, ['X-WP-TotalPages' => 1]),
            'https://wp.example/wp-json/wp/v2/properties*' => Http::response([], 200, ['X-WP-TotalPages' => 1]),
        ]);

        $result = app(WpRestImportService::class)->fetch('https://wp.example');
        $this->assertCount(2, $result['pages']);
    }

    public function test_media_download_blocked_for_private_ip_fixture(): void
    {
        $this->assertFalse(app(RemoteMediaDownloader::class)->isSafeUrl('http://127.0.0.1/image.jpg'));
    }

    public function test_idempotence_still_holds(): void
    {
        $this->seed();
        $xml = File::get(base_path('tests/Fixtures/sample-wp-export.xml'));

        $run1 = MigrationRun::query()->create(['source_type' => 'wxr_xml', 'status' => 'queued', 'options' => ['source_type' => 'wxr_xml', 'wxr_content' => $xml, 'overwrite_strategy' => 'skip_if_exists']]);
        app(MigrationManager::class)->processRun($run1);
        $run2 = MigrationRun::query()->create(['source_type' => 'wxr_xml', 'status' => 'queued', 'options' => ['source_type' => 'wxr_xml', 'wxr_content' => $xml, 'overwrite_strategy' => 'skip_if_exists']]);
        app(MigrationManager::class)->processRun($run2);

        $this->assertSame(1, \App\Models\CmsPages\Page::query()->where('slug', 'about-restinnov')->count());
    }

    public function test_non_regressions_install_properties_blog_owner_and_booking_confirm(): void
    {
        $this->seed();
        File::put(storage_path('app/install.lock'), 'locked');
        $this->get('/install')->assertRedirect(route('admin.login'));
        File::delete(storage_path('app/install.lock'));

        $admin = User::factory()->create();
        $owner = User::factory()->create(['role' => 'owner']);
        $property = \App\Models\RealEstate\Property::query()->where('status', 'published')->firstOrFail();
        $request = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2031-01-10',
            'checkout_date' => '2031-01-12',
            'guests' => 2,
            'full_name' => 'P10',
            'email' => 'p10@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$request->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', ['booking_request_id' => $request->id]);
        $this->get('/our-properties')->assertOk();
        $this->get('/blog')->assertOk();
        $this->actingAs($owner)->get('/owner')->assertOk();
    }
}
