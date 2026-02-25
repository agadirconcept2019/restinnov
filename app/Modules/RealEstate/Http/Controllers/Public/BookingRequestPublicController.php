<?php

namespace App\Modules\RealEstate\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Modules\RealEstate\Http\Requests\Public\StoreBookingRequest;
use App\Modules\RealEstate\Services\PricingEstimator;

class BookingRequestPublicController extends Controller
{
    public function store(StoreBookingRequest $request, Property $property, PricingEstimator $pricingEstimator)
    {
        $blocked = $property->availabilities()
            ->whereBetween('date', [$request->string('checkin_date'), date('Y-m-d', strtotime($request->string('checkout_date').' -1 day'))])
            ->whereIn('status', ['blocked', 'booked'])
            ->exists();

        if ($blocked) {
            return back()->withErrors(['checkin_date' => 'Selected dates are not available.'])->withInput();
        }

        $estimated = $pricingEstimator->estimate($property, $request->string('checkin_date'), $request->string('checkout_date'));

        $property->bookingRequests()->create($request->validated() + [
            'status' => 'new',
            'estimated_total' => $estimated,
            'locale' => app()->getLocale(),
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => (string) $request->userAgent(),
            'source_url' => (string) url()->previous(),
        ]);

        return back()->with('status', 'Booking request sent successfully.');
    }
}
