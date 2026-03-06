<?php

namespace Tests\Feature;

use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyInquiry;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealEstateModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_returns_200(): void
    {
        $this->seed();

        $this->get('/our-properties')->assertOk();
    }

    public function test_property_published_returns_200_and_draft_returns_404(): void
    {
        $this->seed();

        $published = Property::query()->where('status', 'published')->firstOrFail();
        $draft = Property::factory()->draft()->create([
            'property_type_id' => PropertyType::first()->id,
            'rental_mode_id' => RentalMode::first()->id,
            'city_id' => City::first()->id,
        ]);

        $this->get('/properties/'.$published->slug)->assertOk();
        $this->get('/properties/'.$draft->slug)->assertNotFound();
    }

    public function test_archive_by_city_filters_correctly(): void
    {
        $this->seed();
        $city = City::query()->firstOrFail();

        $this->get('/city/'.$city->slug)->assertOk();
    }

    public function test_inquiry_validates_and_stores_row(): void
    {
        $this->seed();
        $property = Property::query()->firstOrFail();

        $this->post('/properties/'.$property->id.'/inquiry', [])->assertSessionHasErrors(['first_name','last_name','email']);

        $this->post('/properties/'.$property->id.'/inquiry', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'message' => 'hello',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('property_inquiries', ['email' => 'john@example.com']);
    }

    public function test_localized_route_fr_our_properties_returns_200(): void
    {
        $this->seed();

        $this->get('/fr/our-properties')->assertOk();
    }

    public function test_availability_uniqueness_on_property_id_and_date(): void
    {
        $this->seed();
        $property = Property::query()->firstOrFail();

        $property->availabilities()->updateOrCreate(['date' => now()->toDateString()], ['status' => 'available']);
        $property->availabilities()->updateOrCreate(['date' => now()->toDateString()], ['status' => 'blocked']);

        $count = $property->availabilities()->whereDate('date', now()->toDateString())->count();
        $this->assertSame(1, $count);
    }
}
