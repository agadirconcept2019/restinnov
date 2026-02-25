<?php

namespace Tests\Feature;

use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyIcalFeed;
use App\Modules\RealEstate\Jobs\SyncIcalFeedJob;
use App\Modules\RealEstate\Services\IcalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RealEstatePhase6Test extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_by_city(): void
    {
        $this->seed();

        $this->get('/our-properties?city=agadir')->assertOk()->assertSee('Agadir');
    }

    public function test_search_excludes_unavailable_for_date_range(): void
    {
        $this->seed();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $property->availabilities()->updateOrCreate(['date' => '2030-01-10'], ['status' => 'booked']);

        $this->get('/our-properties?checkin=2030-01-10&checkout=2030-01-12')->assertOk()->assertDontSee($property->slug);
    }

    public function test_localized_route_keeps_locale(): void
    {
        $this->seed();

        $this->get('/fr/our-properties?city=agadir')->assertOk()->assertSee('/fr/our-properties', false);
    }

    public function test_booking_request_success_for_available_range(): void
    {
        $this->seed();
        $property = Property::query()->where('status', 'published')->firstOrFail();

        $this->post('/properties/'.$property->id.'/booking-request', [
            'checkin_date' => now()->addDays(2)->toDateString(),
            'checkout_date' => now()->addDays(4)->toDateString(),
            'guests' => 2,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('booking_requests', ['property_id' => $property->id, 'status' => 'new']);
    }

    public function test_booking_request_rejected_when_blocked(): void
    {
        $this->seed();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $date = now()->addDays(5)->toDateString();
        $property->availabilities()->updateOrCreate(['date' => $date], ['status' => 'blocked']);

        $this->post('/properties/'.$property->id.'/booking-request', [
            'checkin_date' => $date,
            'checkout_date' => now()->addDays(7)->toDateString(),
            'guests' => 2,
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
        ])->assertSessionHasErrors();
    }

    public function test_admin_confirm_marks_days_booked(): void
    {
        $this->seed();
        $user = \App\Models\User::factory()->create();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $request = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2030-01-10',
            'checkout_date' => '2030-01-12',
            'guests' => 2,
            'full_name' => 'Admin Case',
            'email' => 'a@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($user)->post('/admin/real-estate/booking-requests/'.$request->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('property_availabilities', ['property_id' => $property->id, 'date' => '2030-01-10', 'status' => 'booked']);
    }

    public function test_ssrf_guard_rejects_localhost(): void
    {
        $this->assertFalse(app(IcalService::class)->validateUrl('http://127.0.0.1/calendar.ics'));
    }

    public function test_sync_ical_job_parses_fixture_and_blocks_dates(): void
    {
        $this->seed();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $feed = PropertyIcalFeed::query()->create(['property_id' => $property->id, 'feed_url' => 'https://example.com/c.ics', 'is_active' => true]);

        Http::fake(['https://example.com/c.ics' => Http::response(File::get(base_path('tests/Fixtures/sample-calendar.ics')), 200)]);

        (new SyncIcalFeedJob($feed->id))->handle(app(IcalService::class));

        $this->assertDatabaseHas('property_availabilities', ['property_id' => $property->id, 'date' => '2030-01-10', 'status' => 'booked']);
    }

    public function test_calendar_ics_route_returns_calendar_content(): void
    {
        $this->seed();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $property->availabilities()->updateOrCreate(['date' => '2030-01-10'], ['status' => 'booked']);

        $this->get('/properties/'.$property->slug.'/calendar.ics')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertSee('BEGIN:VCALENDAR', false)
            ->assertSee('DTSTART;VALUE=DATE:20300110', false);
    }

    public function test_non_regressions_install_blog_services_properties(): void
    {
        $this->seed();
        File::put(storage_path('app/install.lock'), 'locked');
        $this->get('/install')->assertRedirect(route('admin.login'));
        File::delete(storage_path('app/install.lock'));

        $this->get('/blog')->assertOk();
        $this->get('/our-services')->assertOk();
        $this->get('/our-properties')->assertOk();
    }
}
