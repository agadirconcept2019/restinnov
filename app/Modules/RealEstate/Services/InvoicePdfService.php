<?php

namespace App\Modules\RealEstate\Services;

use App\Models\Core\Setting;
use App\Models\RealEstate\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function ensureGenerated(Invoice $invoice): string
    {
        $enabled = (bool) (Setting::query()->where('group', 'invoices')->where('key', 'pdf_enabled')->value('value') ?? false);
        $base = 'invoices/'.$invoice->invoice_number;

        if ($enabled && class_exists(Pdf::class)) {
            $path = $base.'.pdf';
            if (! Storage::disk('local')->exists($path)) {
                $pdf = Pdf::loadView('realestate::admin.bookings.invoice', ['booking' => $invoice->booking()->with('property.translations')->first()]);
                Storage::disk('local')->put($path, $pdf->output());
            }
            $invoice->update(['pdf_path' => $path]);

            return $path;
        }

        $path = $base.'.html';
        if (! Storage::disk('local')->exists($path)) {
            $html = view('realestate::admin.bookings.invoice', ['booking' => $invoice->booking()->with('property.translations')->first()])->render();
            Storage::disk('local')->put($path, $html);
        }
        $invoice->update(['pdf_path' => $path]);

        return $path;
    }

    public function download(Invoice $invoice)
    {
        $path = $this->ensureGenerated($invoice);
        $booking = $invoice->booking()->with('property.translations')->first();

        if (str_ends_with($path, '.pdf')) {
            return response(Storage::disk('local')->get($path), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
                'Cache-Control' => 'private, no-store',
                'X-Robots-Tag' => 'noindex, nofollow',
            ]);
        }

        return response()->view('realestate::admin.bookings.invoice', compact('booking'), 200, [
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
