<?php

namespace Tests\Feature;

use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyInquiry;
use App\Models\User;
use App\Modules\OwnerPortal\Jobs\SendOwnerPortalMailJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class OwnerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_dashboard_requires_auth_and_loads(): void
    {
        $this->seed();
        $this->get('/owner')->assertRedirect(route('admin.login'));

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner)->get('/owner')->assertOk();
    }

    public function test_owner_cannot_access_property_not_assigned(): void
    {
        $this->seed();
        $owner = User::factory()->create(['role' => 'owner']);
        $property = Property::query()->firstOrFail();

        $this->actingAs($owner)->get('/owner/properties/'.$property->id)->assertForbidden();
    }

    public function test_owner_booking_list_shows_only_assigned_properties(): void
    {
        $this->seed();
        $owner = User::factory()->create(['role' => 'owner']);
        $property = Property::query()->firstOrFail();
        $property->update(['owner_user_id' => $owner->id]);

        BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => now()->addDays(3)->toDateString(),
            'checkout_date' => now()->addDays(5)->toDateString(),
            'guests' => 2,
            'full_name' => 'Client A',
            'email' => 'clienta@example.com',
            'status' => 'new',
        ]);

        $otherOwner = User::factory()->create(['role' => 'owner']);
        $otherProperty = Property::query()->whereKeyNot($property->id)->firstOrFail();
        $otherProperty->update(['owner_user_id' => $otherOwner->id]);
        BookingRequest::query()->create([
            'property_id' => $otherProperty->id,
            'checkin_date' => now()->addDays(3)->toDateString(),
            'checkout_date' => now()->addDays(5)->toDateString(),
            'guests' => 2,
            'full_name' => 'Client B',
            'email' => 'clientb@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($owner)->get('/owner/booking-requests')->assertOk()->assertSee($property->translated()?->title)->assertDontSee($otherProperty->translated()?->title);
    }

    public function test_owner_confirm_booking_marks_availability_booked(): void
    {
        $this->seed();
        $owner = User::factory()->create(['role' => 'owner']);
        $property = Property::query()->firstOrFail();
        $property->update(['owner_user_id' => $owner->id]);

        $booking = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => '2030-03-10',
            'checkout_date' => '2030-03-12',
            'guests' => 2,
            'full_name' => 'Client C',
            'email' => 'clientc@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($owner)->post('/owner/booking-requests/'.$booking->id.'/status', ['status' => 'confirmed'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('property_availabilities', ['property_id' => $property->id, 'date' => '2030-03-10', 'status' => 'booked']);
    }

    public function test_owner_notes_create_and_visibility(): void
    {
        $this->seed();
        $owner = User::factory()->create(['role' => 'owner']);
        $property = Property::query()->firstOrFail();
        $property->update(['owner_user_id' => $owner->id]);
        $booking = BookingRequest::query()->create([
            'property_id' => $property->id,
            'checkin_date' => now()->addDays(3)->toDateString(),
            'checkout_date' => now()->addDays(5)->toDateString(),
            'guests' => 2,
            'full_name' => 'Client D',
            'email' => 'clientd@example.com',
            'status' => 'new',
        ]);

        $this->actingAs($owner)->post('/owner/booking-requests/'.$booking->id.'/notes', ['note' => 'Owner note', 'visibility' => 'owner'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('crm_notes', ['entity_type' => 'booking_request', 'entity_id' => $booking->id, 'visibility' => 'owner']);
    }

    public function test_email_dispatch_queued_and_does_not_break_request(): void
    {
        $this->seed();
        $owner = User::factory()->create(['role' => 'owner']);
        $property = Property::query()->firstOrFail();
        $property->update(['owner_user_id' => $owner->id]);
        $inquiry = PropertyInquiry::query()->create([
            'property_id' => $property->id,
            'first_name' => 'Client',
            'last_name' => 'E',
            'email' => 'cliente@example.com',
            'status' => 'new',
        ]);

        Bus::fake();
        $this->actingAs($owner)->post('/owner/inquiries/'.$inquiry->id.'/reply', ['message' => 'Hello there'])->assertSessionHasNoErrors();
        Bus::assertDispatched(SendOwnerPortalMailJob::class);
    }

    public function test_non_regression_routes_still_work(): void
    {
        $this->seed();
        File::put(storage_path('app/install.lock'), 'locked');
        $this->get('/install')->assertRedirect(route('admin.login'));
        File::delete(storage_path('app/install.lock'));

        $this->get('/our-properties')->assertOk();
        $this->get('/blog')->assertOk();
        $this->get('/our-services')->assertOk();
    }
}
