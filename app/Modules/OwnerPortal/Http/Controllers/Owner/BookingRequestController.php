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
use App\Modules\RealEstate\Services\BookingService;
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

    public function status(UpdateOwnerBookingStatusRequest $request, BookingRequest $bookingRequest, BookingService $bookingService, AuditLogger $auditLogger)
    {
        $this->ensureOwnsProperty($bookingRequest->property);

        $status = $request->string('status')->value();

        try {
            if ($status === 'confirmed') {
                $bookingService->confirmFromRequest($bookingRequest);
            } else {
                DB::transaction(function () use ($bookingRequest, $status, $auditLogger) {
                    $bookingRequest->update(['status' => $status]);
                    $auditLogger->log('owner.booking.status_changed', $bookingRequest, ['status' => $status]);
                });
            }
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['status' => $exception->getMessage()]);
        }

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
