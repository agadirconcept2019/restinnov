<?php

namespace Tests\Feature;

use App\Core\Mail\TemplateRenderer;
use App\Jobs\SendTemplatedEmailJob;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Invoice;
use App\Models\RealEstate\Property;
use App\Models\User;
use App\Modules\Communications\Models\EmailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase10BCommunicationsTest extends TestCase
{
    use RefreshDatabase;


    private function makeBookingWithInvoice(): array
    {
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $booking = Booking::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2032-02-10',
            'checkout_date' => '2032-02-12',
            'nights' => 2,
            'guests' => 2,
            'guest_full_name' => 'Client',
            'guest_email' => 'client@example.com',
            'status' => 'confirmed',
            'currency' => 'MAD',
            'subtotal' => 100,
            'taxes_total' => 0,
            'fees_total' => 0,
            'discount_total' => 0,
            'total' => 100,
            'confirmed_at' => now(),
        ]);
        $invoice = Invoice::query()->create([
            'booking_id' => $booking->id,
            'invoice_number' => 'INV-2032-000001',
            'issued_at' => now(),
            'currency' => 'MAD',
            'subtotal' => 100,
            'taxes_total' => 0,
            'total' => 100,
            'status' => 'issued',
        ]);

        return [$booking, $invoice];
    }


    public function test_booking_confirm_creates_email_log_queued(): void
    {
        $this->seed();
        Bus::fake();
        $admin = User::factory()->create();
        $property = Property::query()->where('status', 'published')->firstOrFail();
        $request = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2032-01-10',
            'checkout_date' => '2032-01-12',
            'guests' => 2,
            'full_name' => 'C',
            'email' => 'client@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($admin)->post('/admin/real-estate/booking-requests/'.$request->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('email_logs', ['template_key' => 'booking.confirmed', 'status' => 'queued']);
        Bus::assertDispatched(SendTemplatedEmailJob::class);
    }

    public function test_send_templated_email_job_sets_sent_or_failed_and_attempts(): void
    {
        $this->seed();
        $log = EmailLog::query()->create([
            'template_key' => 'booking.confirmed',
            'locale' => 'en',
            'to_email_masked' => 'c***@example.com',
            'to_email_encrypted' => encrypt('client@example.com'),
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        Mail::fake();
        (new SendTemplatedEmailJob('client@example.com', 'booking.confirmed', [], 'en', $log->id))->handle(app(TemplateRenderer::class));
        $log->refresh();
        $this->assertSame('sent', $log->status);
        $this->assertSame(1, $log->attempts);

        Mail::shouldReceive('html')->andThrow(new \RuntimeException('smtp err'));
        (new SendTemplatedEmailJob('client@example.com', 'booking.confirmed', [], 'en', $log->id))->handle(app(TemplateRenderer::class));
        $log->refresh();
        $this->assertSame('failed', $log->status);
        $this->assertSame(2, $log->attempts);
    }

    public function test_retry_action_requeues_if_attempts_less_than_three(): void
    {
        $this->seed();
        Bus::fake();
        $admin = User::factory()->create();
        $log = EmailLog::query()->create([
            'template_key' => 'booking.confirmed',
            'locale' => 'en',
            'to_email_masked' => 'c***@example.com',
            'to_email_encrypted' => encrypt('client@example.com'),
            'payload_encrypted' => encrypt(json_encode(['variables' => []], JSON_THROW_ON_ERROR)),
            'status' => 'failed',
            'attempts' => 2,
            'failed_at' => now(),
        ]);

        $this->actingAs($admin)->post('/admin/communications/logs/'.$log->id.'/retry')->assertSessionHasNoErrors();
        Bus::assertDispatched(SendTemplatedEmailJob::class);

        $log->update(['attempts' => 3, 'status' => 'failed']);
        $this->actingAs($admin)->post('/admin/communications/logs/'.$log->id.'/retry')->assertSessionHasNoErrors();
    }

    public function test_template_preview_renders_safely(): void
    {
        $render = app(TemplateRenderer::class)->render('booking.confirmed', ['property_title' => '<script>alert(1)</script>'], 'en');
        $this->assertStringNotContainsString('<script>', $render['body_html']);
    }

    public function test_signed_invoice_link_works_and_expires_and_booking_summary_works(): void
    {
        $this->seed();
        [$booking, $invoice] = $this->makeBookingWithInvoice();

        $url = \URL::temporarySignedRoute('realestate.client.invoice.download', now()->addMinute(), ['invoice' => $invoice->id]);
        $this->get($url)->assertOk();
        $this->travel(2)->minutes();
        $this->get($url)->assertForbidden();

        $summary = \URL::temporarySignedRoute('realestate.client.booking.summary', now()->addMinutes(5), ['booking' => $booking->id]);
        $this->get($summary)->assertOk();
    }

    public function test_invoice_pdf_fallback_html_and_admin_routes_protected(): void
    {
        $this->seed();
        [$booking, $invoice] = $this->makeBookingWithInvoice();
        $this->get('/admin/communications/templates')->assertRedirect('/admin/login');
        $admin = User::factory()->create();

        $this->actingAs($admin)->get('/admin/communications/templates')->assertOk();
        $this->actingAs($admin)->get('/admin/communications/logs')->assertOk();
        $this->actingAs($admin)->get('/admin/real-estate/bookings/'.$booking->id.'/invoice/download')->assertOk();
    }

    public function test_non_regressions_install_public_and_owner_and_sitemap(): void
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
        $this->get('/sitemap.xml')->assertOk();
    }
}
