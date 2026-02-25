<?php

namespace App\Modules\RealEstate\Services;

use App\Models\RealEstate\Property;

class PricingEstimator
{
    public function estimate(Property $property, string $checkin, string $checkout): float
    {
        $start = new \DateTimeImmutable($checkin);
        $end = new \DateTimeImmutable($checkout);

        if ($end <= $start) {
            return 0.0;
        }

        $map = $property->availabilities()
            ->whereBetween('date', [$checkin, $end->modify('-1 day')->format('Y-m-d')])
            ->get()
            ->keyBy(fn ($a) => $a->date);

        $total = 0.0;
        for ($day = $start; $day < $end; $day = $day->modify('+1 day')) {
            $key = $day->format('Y-m-d');
            $total += (float) ($map[$key]->price_per_night ?? $property->base_price_per_night);
        }

        return round($total, 2);
    }
}
