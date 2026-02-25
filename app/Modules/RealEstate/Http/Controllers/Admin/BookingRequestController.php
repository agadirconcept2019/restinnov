<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Core\Audit\AuditLogger;
use App\Core\Lock\LockService;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\BookingRequest;
use App\Modules\RealEstate\Services\AvailabilityServiceV2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function updateStatus(Request $request, BookingRequest $bookingRequest, AvailabilityServiceV2 $availabilityService, AuditLogger $auditLogger, LockService $lockService)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected', 'canceled', 'expired'])],
        ]);

        try {
            $result = $lockService->runWithLock('realestate:booking:'.$bookingRequest->id, 15, function () use ($data, $bookingRequest, $availabilityService, $auditLogger) {
                return DB::transaction(function () use ($data, $bookingRequest, $availabilityService, $auditLogger) {
                /** @var BookingRequest $lockedBooking */
                $lockedBooking = BookingRequest::query()->lockForUpdate()->findOrFail($bookingRequest->id);

                if ($data['status'] === 'confirmed') {
                    if ($lockedBooking->status === 'confirmed') {
                        throw new \RuntimeException('Booking request is already confirmed.');
                    }

                    $existsConflict = $lockedBooking->property->availabilities()
                        ->whereBetween('date', [$lockedBooking->checkin_date->toDateString(), $lockedBooking->checkout_date->copy()->subDay()->toDateString()])
                        ->lockForUpdate()
                        ->whereIn('status', ['booked', 'blocked'])
                        ->exists();

                    if ($existsConflict) {
                        throw new \RuntimeException('Selected dates are no longer available.');
                    }
                }

                $lockedBooking->update(['status' => $data['status']]);

                if ($data['status'] === 'confirmed') {
                    $availabilityService->bulkUpdate(
                        $lockedBooking->property_id,
                        $lockedBooking->checkin_date->format('Y-m-d'),
                        $lockedBooking->checkout_date->copy()->subDay()->format('Y-m-d'),
                        ['status' => 'booked'],
                    );
                }

                $auditLogger->log('realestate.booking.status_changed', $lockedBooking, ['status' => $data['status']]);

                    return true;
                });
            });
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

        if ($result === null) {
            return back()->withErrors(['status' => 'Another update is in progress for this booking request.']);
        }

        return back()->with('status', 'Booking request updated.');
    }
}
