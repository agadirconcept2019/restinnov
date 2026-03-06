<?php

namespace Tests\Feature;

use App\Core\Cache\CacheVersionManager;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyIcalFeed;
use App\Models\User;
use App\Modules\RealEstate\Jobs\SyncIcalFeedJob;
use App\Modules\RealEstate\Services\IcalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase8HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_ical_job_is_skipped_when_global_lock_exists(): void
    {
        $this->seed();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $feed = PropertyIcalFeed::query()->create(['property_id' => $property->id, 'feed_url' => 'https://example.com/c.ics', 'is_active' => true]);

        Http::fake(['https://example.com/c.ics' => Http::response(File::get(base_path('tests/Fixtures/sample-calendar.ics')), 200)]);

        $lock = Cache::lock('realestate:ical:global', 10);
        $lock->get();

        (new SyncIcalFeedJob($feed->id))->handle(app(IcalService::class), app(\App\Core\Lock\LockService::class));

        $this->assertDatabaseMissing('property_sync_logs', ['property_ical_feed_id' => $feed->id]);

        $lock->release();
    }

    public function test_booking_confirm_second_attempt_fails_safely(): void
    {
        $this->seed();
        $admin = User::factory()->create();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $booking = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2030-06-10',
            'checkout_date' => '2030-06-12',
            'guests' => 2,
            'full_name' => 'Case',
            'email' => 'case@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$booking->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$booking->id.'/status', ['status' => 'confirmed'])->assertSessionHasErrors('status');

        $this->assertSame(2, $property->availabilities()->where('status', 'booked')->whereBetween('date', ['2030-06-10', '2030-06-11'])->count());
    }

    public function test_bulk_availability_range_limit_is_enforced(): void
    {
        $this->seed();
        $admin = User::factory()->create();
        $property = Property::query()->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/real-estate/availability/bulk-update', [
                'property_id' => $property->id,
                'from_date' => '2030-01-01',
                'to_date' => '2030-04-15',
                'status' => 'blocked',
            ])
            ->assertSessionHasErrors('to_date');
    }

    public function test_cache_invalidation_version_is_bumped_when_property_updates(): void
    {
        $this->seed();
        $manager = app(CacheVersionManager::class);
        $before = $manager->versionedKey('realestate.search', app()->getLocale(), 'abc');

        $property = Property::query()->firstOrFail();
        $property->update(['is_featured' => ! $property->is_featured]);

        $after = $manager->versionedKey('realestate.search', app()->getLocale(), 'abc');
        $this->assertNotSame($before, $after);
    }

    public function test_health_route_returns_ok_json(): void
    {
        $this->get('/health')->assertOk()->assertJsonPath('ok', true);
    }

    public function test_phase8_non_regression_routes(): void
    {
        $this->seed();
        File::put(storage_path('app/install.lock'), 'locked');
        $this->get('/install')->assertRedirect(route('admin.login'));
        File::delete(storage_path('app/install.lock'));

        $owner = User::factory()->create(['role' => 'owner']);

        $this->get('/our-properties')->assertOk();
        $this->get('/blog')->assertOk();
        $this->get('/our-services')->assertOk();
        $this->actingAs($owner)->get('/owner')->assertOk();
    }
}
