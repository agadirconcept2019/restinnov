<?php

namespace App\Services\RealEstate;

use App\Models\RealEstate\PropertyAvailability;

class AvailabilityService
{
    public function setStatus(int $propertyId, string $date, string $status): PropertyAvailability
    {
        return PropertyAvailability::query()->updateOrCreate(
            ['property_id' => $propertyId, 'date' => $date],
            ['status' => $status],
        );
    }
}
