<?php

namespace App\Modules\RealEstate\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\Invoice;
use App\Modules\RealEstate\Services\InvoicePdfService;

class ClientAccessController extends Controller
{
    public function bookingSummary(Booking $booking)
    {
        if ($booking->status === 'canceled') {
            abort(404);
        }

        $booking->load('property.translations', 'invoice');

        return response()->view('realestate::client.booking-summary', compact('booking'), 200, [
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function invoiceDownload(Invoice $invoice, InvoicePdfService $invoicePdfService)
    {
        if (in_array($invoice->status, ['void'], true) || $invoice->booking?->status === 'canceled') {
            abort(404);
        }

        return $invoicePdfService->download($invoice);
    }
}
