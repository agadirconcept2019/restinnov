<?php

namespace Tests\Feature;

use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_200(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_properties_listing_returns_200(): void
    {
        Property::factory()->create();

        $this->get('/our-properties')->assertOk();
    }

    public function test_published_property_returns_200_and_draft_returns_404(): void
    {
        $published = Property::factory()->create();
        $draft = Property::factory()->draft()->create();

        $this->get('/properties/'.$published->slug)->assertOk();
        $this->get('/properties/'.$draft->slug)->assertNotFound();
    }

    public function test_archive_by_city_filters_correctly(): void
    {
        $paris = City::factory()->create(['slug' => 'paris']);
        $lyon = City::factory()->create(['slug' => 'lyon']);
        Property::factory()->create(['city_id' => $paris->id, 'title' => 'Paris Flat']);
        Property::factory()->create(['city_id' => $lyon->id, 'title' => 'Lyon Flat']);

        $this->get('/city/paris')->assertOk()->assertSee('Paris Flat')->assertDontSee('Lyon Flat');
    }

    public function test_localized_route_works(): void
    {
        $this->get('/fr')->assertOk();
    }
}
