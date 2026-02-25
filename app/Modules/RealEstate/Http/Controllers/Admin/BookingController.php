<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Booking;
use App\Modules\RealEstate\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::query()
            ->with('property.translations', 'invoice')
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->when(request('email'), fn ($q, $v) => $q->where('guest_email', 'like', "%{$v}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('realestate::admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load('property.translations', 'items', 'invoice');

        return view('realestate::admin.bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking, BookingService $bookingService)
    {
        $bookingService->cancel($booking);

        return back()->with('status', 'Booking canceled.');
    }

    public function invoice(Booking $booking)
    {
        $booking->load('invoice', 'property.translations');

        return view('realestate::admin.bookings.invoice', compact('booking'));
    }

    public function invoiceDownload(Booking $booking)
    {
        $booking->load('invoice', 'property.translations');

        return response()->view('realestate::admin.bookings.invoice', compact('booking'));
    }
}
