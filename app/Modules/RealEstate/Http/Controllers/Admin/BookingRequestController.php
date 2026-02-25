<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\BookingRequest;
use App\Modules\RealEstate\Services\BookingService;
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
        $bookingRequest->load('property.translations', 'booking.invoice');

        return view('realestate::admin.booking-requests.show', compact('bookingRequest'));
    }

    public function updateStatus(Request $request, BookingRequest $bookingRequest, BookingService $bookingService, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected', 'canceled', 'expired'])],
        ]);

        try {
            if ($data['status'] === 'confirmed') {
                $bookingService->confirmFromRequest($bookingRequest);
            } else {
                $bookingRequest->update(['status' => $data['status']]);
                $auditLogger->log('realestate.booking_request.status_changed', $bookingRequest, ['status' => $data['status']]);
            }
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Booking request updated.');
    }
}
