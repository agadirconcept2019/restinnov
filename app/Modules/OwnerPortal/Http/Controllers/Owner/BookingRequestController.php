<?php

namespace App\Modules\OwnerPortal\Http\Controllers\Owner;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\BookingRequest;
use App\Modules\OwnerPortal\Http\Requests\Owner\StoreCrmNoteRequest;
use App\Modules\OwnerPortal\Http\Requests\Owner\UpdateOwnerBookingStatusRequest;
use App\Modules\OwnerPortal\Jobs\SendOwnerPortalMailJob;
use App\Modules\OwnerPortal\Models\CrmNote;
use App\Modules\OwnerPortal\Support\OwnerAccess;
use App\Modules\RealEstate\Services\AvailabilityServiceV2;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class BookingRequestController extends Controller
{
    use OwnerAccess;

    public function index()
    {
        $this->ensureOwnerRole();
        $ownerId = auth()->id();

        $bookingRequests = BookingRequest::query()
            ->with('property.translations')
            ->whereHas('property', fn ($q) => $q->where('owner_user_id', $ownerId))
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('ownerportal::owner.booking-requests.index', compact('bookingRequests'));
    }

    public function show(BookingRequest $bookingRequest)
    {
        $this->ensureOwnsProperty($bookingRequest->property);
        $notes = CrmNote::query()->where('entity_type', 'booking_request')->where('entity_id', $bookingRequest->id)->latest()->get();

        return view('ownerportal::owner.booking-requests.show', compact('bookingRequest', 'notes'));
    }

    public function status(UpdateOwnerBookingStatusRequest $request, BookingRequest $bookingRequest, AvailabilityServiceV2 $availabilityService, AuditLogger $auditLogger)
    {
        $this->ensureOwnsProperty($bookingRequest->property);

        DB::transaction(function () use ($request, $bookingRequest, $availabilityService, $auditLogger) {
            $status = $request->string('status')->value();
            $bookingRequest->update(['status' => $status]);

            if ($status === 'confirmed') {
                $availabilityService->bulkUpdate(
                    $bookingRequest->property_id,
                    $bookingRequest->checkin_date->format('Y-m-d'),
                    $bookingRequest->checkout_date->copy()->subDay()->format('Y-m-d'),
                    ['status' => 'booked'],
                );
            }

            $auditLogger->log('owner.booking.status_changed', $bookingRequest, ['status' => $status]);
        });

        try {
            Bus::dispatch(new SendOwnerPortalMailJob($bookingRequest->email, 'Booking request update', 'Your booking request status is now '.$bookingRequest->status.'.'));
        } catch (\Throwable) {
            // never break UX
        }

        return back()->with('status', 'Booking request status updated.');
    }

    public function note(StoreCrmNoteRequest $request, BookingRequest $bookingRequest)
    {
        $this->ensureOwnsProperty($bookingRequest->property);

        CrmNote::query()->create([
            'entity_type' => 'booking_request',
            'entity_id' => $bookingRequest->id,
            'author_user_id' => auth()->id(),
            'note' => $request->string('note')->value(),
            'visibility' => $request->string('visibility', 'owner')->value(),
        ]);

        return back()->with('status', 'Note added.');
    }
}
