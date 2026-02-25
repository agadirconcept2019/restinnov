<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\BookingRequest;
use App\Modules\RealEstate\Services\AvailabilityServiceV2;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingRequestController extends Controller
{
    public function index()
    {
        $bookingRequests = BookingRequest::query()
            ->with('property.translations')
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('realestate::admin.booking-requests.index', compact('bookingRequests'));
    }

    public function show(BookingRequest $bookingRequest)
    {
        $bookingRequest->load('property.translations');

        return view('realestate::admin.booking-requests.show', compact('bookingRequest'));
    }

    public function updateStatus(Request $request, BookingRequest $bookingRequest, AvailabilityServiceV2 $availabilityService, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected', 'canceled', 'expired'])],
        ]);

        $bookingRequest->update(['status' => $data['status']]);

        if ($data['status'] === 'confirmed') {
            $availabilityService->bulkUpdate(
                $bookingRequest->property_id,
                $bookingRequest->checkin_date->format('Y-m-d'),
                $bookingRequest->checkout_date->copy()->subDay()->format('Y-m-d'),
                ['status' => 'booked'],
            );
        }

        $auditLogger->log('realestate.booking.status_changed', $bookingRequest, ['status' => $data['status']]);

        return back()->with('status', 'Booking request updated.');
    }
}
