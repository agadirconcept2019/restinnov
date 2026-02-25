<?php

namespace Tests\Unit;

use App\Models\RealEstate\Property;
use App\Services\RealEstate\AvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_upserts_availability_by_unique_date(): void
    {
        $property = Property::factory()->create();
        $service = app(AvailabilityService::class);

        $service->setStatus($property->id, '2026-05-01', 'available');
        $service->setStatus($property->id, '2026-05-01', 'blocked');

        $this->assertDatabaseCount('property_availabilities', 1);
        $this->assertDatabaseHas('property_availabilities', ['status' => 'blocked']);
    }
}
