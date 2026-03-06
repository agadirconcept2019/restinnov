<?php

namespace App\Modules\RealEstate\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Modules\RealEstate\Http\Requests\Public\StoreBookingRequest;
use App\Modules\OwnerPortal\Jobs\SendOwnerPortalMailJob;
use App\Modules\RealEstate\Services\PricingEstimator;
use Illuminate\Support\Facades\Bus;

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

        $booking = $property->bookingRequests()->create($request->validated() + [
            'status' => 'new',
            'estimated_total' => $estimated,
            'locale' => app()->getLocale(),
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => (string) $request->userAgent(),
            'source_url' => (string) url()->previous(),
        ]);

        try {
            $adminEmail = config('mail.from.address');
            if ($adminEmail) {
                Bus::dispatch(new SendOwnerPortalMailJob($adminEmail, 'New booking request', 'Booking request #'.$booking->id.' created.'));
            }
            if ($property->owner?->email) {
                Bus::dispatch(new SendOwnerPortalMailJob($property->owner->email, 'New booking request for your property', 'Booking request #'.$booking->id.' created.'));
            }
            Bus::dispatch(new SendOwnerPortalMailJob($booking->email, 'Booking request received', 'We received your booking request and will come back soon.'));
        } catch (\Throwable) {
            // no UX failure
        }

        return back()->with('status', 'Booking request sent successfully.');
    }
}
