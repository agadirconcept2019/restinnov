<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Core\AdminTable\AdminTableQuery;
use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\Booking;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\RealEstate\Services\BookingService;
use App\Modules\RealEstate\Services\InvoicePdfService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(AdminTableQuery $adminTableQuery)
    {
        $query = Booking::query()->with('property.translations', 'invoice');
        $bookings = $adminTableQuery->apply($query, [
            'search' => ['guest_email', 'guest_full_name'],
            'filters' => [
                'status' => fn ($q, $v) => $q->where('status', $v),
            ],
            'sorts' => ['id', 'created_at', 'total', 'status'],
            'default_sort' => 'created_at',
            'default_dir' => 'desc',
        ])->paginate(20)->withQueryString();

        return view('realestate::admin.bookings.index', compact('bookings'));
    }

    public function bulk(Request $request, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:bookings,id'],
            'status' => ['required', 'in:pending,confirmed,canceled'],
        ]);

        Booking::query()->whereIn('id', $data['ids'])->update(['status' => $data['status']]);
        $auditLogger->log('realestate.bookings.bulk_status', null, ['status' => $data['status'], 'count' => count($data['ids'])]);

        return back()->with('status', 'Bookings updated.');
    }

    public function show(Booking $booking)
    {
        $booking->load('property.translations', 'items', 'invoice');
        $communicationLogs = EmailLog::query()
            ->where(function ($q) use ($booking) {
                $q->where(fn ($sq) => $sq->where('related_type', 'booking')->where('related_id', $booking->id));
                if ($booking->invoice) {
                    $q->orWhere(fn ($sq) => $sq->where('related_type', 'invoice')->where('related_id', $booking->invoice->id));
                }
            })
            ->latest()
            ->get();

        return view('realestate::admin.bookings.show', compact('booking', 'communicationLogs'));
    }

    public function cancel(Booking $booking, BookingService $bookingService)
    {
        $bookingService->cancel($booking);

        return back()->with('status', 'Booking canceled.');
    }

    public function resend(Booking $booking, BookingService $bookingService)
    {
        $bookingService->resendConfirmation($booking);

        return back()->with('status', 'Confirmation email queued.');
    }

    public function invoice(Booking $booking)
    {
        $booking->load('invoice', 'property.translations');

        return view('realestate::admin.bookings.invoice', compact('booking'));
    }

    public function invoiceDownload(Booking $booking, InvoicePdfService $invoicePdfService)
    {
        $booking->load('invoice', 'property.translations');

        return $invoicePdfService->download($booking->invoice);
    }
}
