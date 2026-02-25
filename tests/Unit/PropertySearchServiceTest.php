<?php

namespace Tests\Unit;

use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Services\RealEstate\PropertySearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_properties(): void
    {
        $city = City::factory()->create(['slug' => 'paris']);
        Property::factory()->create(['city_id' => $city->id, 'max_guests' => 5]);
        Property::factory()->create(['max_guests' => 2]);

        $result = app(PropertySearchService::class)->search(['city' => 'paris', 'guests' => 4]);

        $this->assertCount(1, $result->items());
    }
}
