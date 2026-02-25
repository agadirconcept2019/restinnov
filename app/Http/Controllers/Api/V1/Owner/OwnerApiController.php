<?php

namespace App\Http\Controllers\Api\V1\Owner;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\BookingResource;
use App\Http\Resources\Api\V1\PropertyResource;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyInquiry;
use Illuminate\Http\Request;

class OwnerApiController extends ApiController
{
    public function properties()
    {
        $properties = Property::query()->where('owner_user_id', auth()->id())->with(['translations', 'city.translations', 'type.translations'])->paginate((int) request('per_page', 20));

        return PropertyResource::collection($properties);
    }

    public function bookings()
    {
        $bookings = Booking::query()->whereHas('property', fn ($q) => $q->where('owner_user_id', auth()->id()))->with('property')->paginate((int) request('per_page', 20));

        return BookingResource::collection($bookings);
    }

    public function inquiries()
    {
        $rows = PropertyInquiry::query()
            ->whereHas('property', fn ($q) => $q->where('owner_user_id', auth()->id()))
            ->paginate((int) request('per_page', 20));

        return response()->json([
            'data' => $rows->through(fn ($i) => [
                'id' => $i->id,
                'property_id' => $i->property_id,
                'status' => $i->status,
                'checkin' => $i->checkin,
                'checkout' => $i->checkout,
                'guests' => $i->guests,
                'email_masked' => str($i->email)->replaceMatches('/(^.).*(@.*$)/', '$1***$2')->toString(),
                'created_at' => $i->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ],
        ]);
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        abort_unless($booking->property && (int) $booking->property->owner_user_id === (int) auth()->id(), 403);

        $data = $request->validate(['status' => ['required', 'in:confirmed,canceled']]);
        $booking->update(['status' => $data['status']]);

        return BookingResource::make($booking->fresh('property'));
    }
}
