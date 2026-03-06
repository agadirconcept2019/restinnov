<?php

namespace App\Modules\RealEstate\Services;

use App\Core\Lock\LockService;
use App\Models\RealEstate\PropertyAvailability;
use Illuminate\Support\Facades\DB;

class AvailabilityServiceV2
{
    public function __construct(private readonly LockService $lockService)
    {
    }

    public function bulkUpdate(int $propertyId, string $fromDate, string $toDate, array $payload): int
    {
        $days = (int) ((strtotime($toDate) - strtotime($fromDate)) / 86400) + 1;
        if ($days < 1 || $days > 90) {
            throw new \InvalidArgumentException('Availability bulk update range must be between 1 and 90 days.');
        }

        $updated = $this->lockService->runWithLock("realestate:availability:property:{$propertyId}", 20, function () use ($propertyId, $fromDate, $toDate, $payload) {
            return DB::transaction(function () use ($propertyId, $fromDate, $toDate, $payload) {
                $updatedRows = 0;
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
                    $updatedRows++;
                    $current = $current->modify('+1 day');
                }

                return $updatedRows;
            });
        });

        if ($updated === null) {
            throw new \RuntimeException('Another availability update is already running for this property.');
        }

        return $updated;
    }
}
