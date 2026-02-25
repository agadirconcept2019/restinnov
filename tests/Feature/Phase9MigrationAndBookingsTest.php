<?php

namespace Tests\Feature;

use App\Core\Mail\TemplateRenderer;
use App\Jobs\SendTemplatedEmailJob;
use App\Models\CmsPages\Page;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Invoice;
use App\Models\RealEstate\Property;
use App\Models\User;
use App\Modules\MigrationTools\Models\MigrationRun;
use App\Modules\MigrationTools\Services\MigrationManager;
use App\Modules\MigrationTools\Services\RemoteMediaDownloader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Phase9MigrationAndBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_write_database(): void
    {
        $this->seed();
        $xml = File::get(base_path('tests/Fixtures/sample-wp-export.xml'));

        $run = MigrationRun::query()->create([
            'source_type' => 'wxr_xml',
            'status' => 'queued',
            'options' => ['source_type' => 'wxr_xml', 'wxr_content' => $xml, 'dry_run' => true],
        ]);

        app(MigrationManager::class)->processRun($run);

        $this->assertDatabaseMissing('pages', ['slug' => 'about-restinnov']);
        $this->assertDatabaseMissing('posts', ['slug' => 'launch-news']);
    }

    public function test_import_pages_posts_and_redirects_are_created(): void
    {
        $this->seed();
        $xml = File::get(base_path('tests/Fixtures/sample-wp-export.xml'));

        $run = MigrationRun::query()->create([
            'source_type' => 'wxr_xml',
            'status' => 'queued',
            'options' => ['source_type' => 'wxr_xml', 'wxr_content' => $xml, 'dry_run' => false, 'create_redirects' => true, 'overwrite_strategy' => 'update_if_exists'],
        ]);

        app(MigrationManager::class)->processRun($run);

        $this->assertDatabaseHas('pages', ['slug' => 'about-restinnov']);
        $this->assertDatabaseHas('posts', ['slug' => 'launch-news']);
        $this->assertDatabaseHas('redirects', ['from_path' => '/about-restinnov']);

        $this->get('/about-restinnov')->assertStatus(301);
    }

    public function test_media_download_blocks_private_ip_url(): void
    {
        $this->assertFalse(app(RemoteMediaDownloader::class)->isSafeUrl('http://127.0.0.1/x.jpg'));
    }

    public function test_idempotent_re_run_does_not_duplicate(): void
    {
        $this->seed();
        $xml = File::get(base_path('tests/Fixtures/sample-wp-export.xml'));

        $run = MigrationRun::query()->create([
            'source_type' => 'wxr_xml',
            'status' => 'queued',
            'options' => ['source_type' => 'wxr_xml', 'wxr_content' => $xml, 'dry_run' => false, 'overwrite_strategy' => 'skip_if_exists'],
        ]);

        app(MigrationManager::class)->processRun($run);
        app(MigrationManager::class)->processRun($run->fresh());

        $this->assertEquals(1, Page::query()->where('slug', 'about-restinnov')->count());
    }

    public function test_confirm_booking_creates_booking_items_invoice_and_books_days(): void
    {
        $this->seed();
        $admin = User::factory()->create();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $request = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2030-08-10',
            'checkout_date' => '2030-08-12',
            'guests' => 2,
            'full_name' => 'Phase9 User',
            'email' => 'phase9@example.com',
            'status' => 'new',
        ]);

        Bus::fake();
        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$request->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();

        $booking = Booking::query()->where('booking_request_id', $request->id)->first();
        $this->assertNotNull($booking);
        $this->assertSame(2, $booking->items()->count());
        $this->assertNotNull($booking->invoice);
        $this->assertDatabaseHas('property_availabilities', ['property_id' => $property->id, 'date' => '2030-08-10', 'status' => 'booked']);
        Bus::assertDispatched(SendTemplatedEmailJob::class);
    }

    public function test_concurrent_confirm_fails_and_invoice_number_unique(): void
    {
        $this->seed();
        $admin = User::factory()->create();
        $property = Property::query()->where('status', 'published')->firstOrFail();

        $make = function ($email) use ($property) {
            return BookingRequest::query()->create([
                'property_id' => $property->id,
                'checkin_date' => '2030-09-10',
                'checkout_date' => '2030-09-12',
                'guests' => 2,
                'full_name' => 'X',
                'email' => $email,
                'status' => 'new',
            ]);
        };

        $first = $make('a@example.com');
        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$first->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$first->id.'/status', ['status' => 'confirmed'])->assertSessionHasErrors('status');

        $second = $make('b@example.com');
        $second->update(['checkin_date' => '2030-09-15', 'checkout_date' => '2030-09-17']);
        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$second->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();

        $numbers = Invoice::query()->pluck('invoice_number')->all();
        $this->assertCount(2, array_unique($numbers));
    }

    public function test_cancel_booking_and_invoice_route(): void
    {
        $this->seed();
        $admin = User::factory()->create();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $request = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2030-10-10',
            'checkout_date' => '2030-10-12',
            'guests' => 2,
            'full_name' => 'Y',
            'email' => 'y@example.com',
            'status' => 'new',
        ]);
        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$request->id.'/status', ['status' => 'confirmed']);

        $booking = Booking::query()->where('booking_request_id', $request->id)->firstOrFail();
        $this->actingAs($admin)->post('/admin/real-estate/bookings/'.$booking->id.'/cancel')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'canceled']);
        $this->assertDatabaseHas('property_availabilities', ['property_id' => $property->id, 'date' => '2030-10-10', 'status' => 'available']);

        $this->actingAs($admin)->get('/admin/real-estate/bookings/'.$booking->id.'/invoice')->assertOk();
    }

    public function test_non_regressions_install_blog_properties_services_owner(): void
    {
        $this->seed();
        File::put(storage_path('app/install.lock'), 'locked');
        $this->get('/install')->assertRedirect(route('admin.login'));
        File::delete(storage_path('app/install.lock'));
        $owner = User::factory()->create(['role' => 'owner']);

        $this->get('/blog')->assertOk();
        $this->get('/our-properties')->assertOk();
        $this->get('/our-services')->assertOk();
        $this->actingAs($owner)->get('/owner')->assertOk();
    }
}
