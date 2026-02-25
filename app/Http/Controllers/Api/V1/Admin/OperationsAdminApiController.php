<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\BookingRequestResource;
use App\Http\Resources\Api\V1\BookingResource;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\PropertyAvailability;
use App\Models\RealEstate\PropertyIcalFeed;
use Illuminate\Http\Request;

class OperationsAdminApiController extends ApiController
{
    public function bookings()
    {
        $bookings = Booking::query()->with('property')->paginate((int) request('per_page', 20))->withQueryString();

        return BookingResource::collection($bookings);
    }

    public function updateBooking(Request $request, Booking $booking)
    {
        $data = $request->validate(['status' => ['required', 'in:pending,confirmed,canceled']]);
        $booking->update(['status' => $data['status']]);

        return BookingResource::make($booking->fresh('property'));
    }

    public function bookingRequests()
    {
        $rows = BookingRequest::query()->with('property')->paginate((int) request('per_page', 20))->withQueryString();

        return BookingRequestResource::collection($rows);
    }

    public function updateBookingRequest(Request $request, BookingRequest $bookingRequest)
    {
        $data = $request->validate(['status' => ['required', 'in:pending,confirmed,rejected,canceled,expired']]);
        $bookingRequest->update(['status' => $data['status']]);

        return BookingRequestResource::make($bookingRequest->fresh('property'));
    }

    public function bulkAvailability(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'status' => ['required', 'in:available,booked,blocked,unavailable'],
        ]);

        $dates = collect(new \DatePeriod(new \DateTime($data['from_date']), new \DateInterval('P1D'), (new \DateTime($data['to_date']))->modify('+1 day')))
            ->map(fn ($d) => $d->format('Y-m-d'));

        foreach ($dates as $date) {
            PropertyAvailability::query()->updateOrCreate(
                ['property_id' => $data['property_id'], 'date' => $date],
                ['status' => $data['status']]
            );
        }

        return response()->json(['updated_days' => $dates->count()]);
    }

    public function icalFeeds()
    {
        return response()->json(PropertyIcalFeed::query()->paginate((int) request('per_page', 20))->toArray());
    }

    public function updateIcalFeed(Request $request, PropertyIcalFeed $icalFeed)
    {
        $data = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'sync_interval_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
        ]);

        $icalFeed->update($data);

        return response()->json($icalFeed->fresh());
    }
}
