<?php

namespace App\Modules\RealEstate\Services;

use App\Models\RealEstate\PropertyAvailability;
use Illuminate\Support\Facades\DB;

class AvailabilityServiceV2
{
    public function bulkUpdate(int $propertyId, string $fromDate, string $toDate, array $payload): int
    {
        return DB::transaction(function () use ($propertyId, $fromDate, $toDate, $payload) {
            $updated = 0;
            $current = new \DateTimeImmutable($fromDate);
            $end = new \DateTimeImmutable($toDate);

            while ($current <= $end) {
                PropertyAvailability::query()->updateOrCreate(
                    ['property_id' => $propertyId, 'date' => $current->format('Y-m-d')],
                    [
                        'status' => $payload['status'] ?? 'available',
                        'price_per_night' => $payload['price_per_night'] ?? null,
                        'minimum_stay' => $payload['minimum_stay'] ?? null,
                    ],
                );
                $updated++;
                $current = $current->modify('+1 day');
            }

            return $updated;
        });
    }
}
