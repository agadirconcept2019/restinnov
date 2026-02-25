<?php

namespace App\Modules\RealEstate\Services;

use App\Core\Audit\AuditLogger;
use App\Core\Lock\LockService;
use App\Jobs\SendTemplatedEmailJob;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Invoice;
use App\Models\RealEstate\PropertyAvailability;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly LockService $lockService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function confirmFromRequest(BookingRequest $bookingRequest): Booking
    {
        $result = $this->lockService->runWithLock('realestate:booking-request-confirm:'.$bookingRequest->id, 20, function () use ($bookingRequest) {
            return DB::transaction(function () use ($bookingRequest) {
                $lockedRequest = BookingRequest::query()->lockForUpdate()->findOrFail($bookingRequest->id);
                if ($lockedRequest->status === 'confirmed' || $lockedRequest->booking()->exists()) {
                    throw new \RuntimeException('Booking request is already confirmed.');
                }

                $from = $lockedRequest->checkin_date->toDateString();
                $to = $lockedRequest->checkout_date->copy()->subDay()->toDateString();

                $conflict = PropertyAvailability::query()
                    ->where('property_id', $lockedRequest->property_id)
                    ->whereBetween('date', [$from, $to])
                    ->lockForUpdate()
                    ->whereIn('status', ['booked', 'blocked'])
                    ->exists();

                if ($conflict) {
                    throw new \RuntimeException('Selected dates are no longer available.');
                }

                $nights = max(1, (int) $lockedRequest->checkin_date->diffInDays($lockedRequest->checkout_date));
                $unitPrice = (float) $lockedRequest->property->base_price_per_night;
                $subtotal = $unitPrice * $nights;

                $booking = Booking::query()->create([
                    'property_id' => $lockedRequest->property_id,
                    'booking_request_id' => $lockedRequest->id,
                    'checkin_date' => $lockedRequest->checkin_date,
                    'checkout_date' => $lockedRequest->checkout_date,
                    'nights' => $nights,
                    'guests' => $lockedRequest->guests,
                    'guest_full_name' => $lockedRequest->full_name,
                    'guest_email' => $lockedRequest->email,
                    'guest_phone' => $lockedRequest->phone,
                    'status' => 'confirmed',
                    'currency' => $lockedRequest->property->currency,
                    'subtotal' => $subtotal,
                    'taxes_total' => 0,
                    'fees_total' => 0,
                    'discount_total' => 0,
                    'total' => $subtotal,
                    'locale' => $lockedRequest->locale,
                    'ip_hash' => $lockedRequest->ip_hash,
                    'user_agent' => $lockedRequest->user_agent,
                    'source_url' => $lockedRequest->source_url,
                    'confirmed_at' => now(),
                ]);

                $current = new \DateTimeImmutable($from);
                $end = new \DateTimeImmutable($to);
                while ($current <= $end) {
                    $date = $current->format('Y-m-d');
                    $booking->items()->create([
                        'date' => $date,
                        'price_per_night' => $unitPrice,
                        'line_total' => $unitPrice,
                    ]);

                    PropertyAvailability::query()->updateOrCreate(
                        ['property_id' => $lockedRequest->property_id, 'date' => $date],
                        ['status' => 'booked'],
                    );

                    $current = $current->modify('+1 day');
                }

                $invoice = Invoice::query()->create([
                    'booking_id' => $booking->id,
                    'invoice_number' => $this->nextInvoiceNumber(),
                    'issued_at' => now(),
                    'currency' => $booking->currency,
                    'subtotal' => $booking->subtotal,
                    'taxes_total' => $booking->taxes_total,
                    'total' => $booking->total,
                    'status' => 'issued',
                ]);

                $lockedRequest->update(['status' => 'confirmed']);

                $this->auditLogger->log('realestate.booking.confirmed', $booking, ['booking_request_id' => $lockedRequest->id, 'invoice_id' => $invoice->id]);

                rescue(function () use ($booking, $invoice) {
                    Bus::dispatch(new SendTemplatedEmailJob(
                        $booking->guest_email,
                        'booking.confirmed',
                        [
                            'property_title' => $booking->property->translated()?->title ?? $booking->property->slug,
                            'checkin' => $booking->checkin_date->toDateString(),
                            'checkout' => $booking->checkout_date->toDateString(),
                            'nights' => $booking->nights,
                            'total' => $booking->total,
                            'invoice_number' => $invoice->invoice_number,
                        ],
                        $booking->locale,
                    ));
                }, report: false);

                return $booking->fresh(['items', 'invoice']);
            });
        });

        if (! $result) {
            throw new \RuntimeException('Another booking confirmation is in progress.');
        }

        return $result;
    }

    public function cancel(Booking $booking): Booking
    {
        return DB::transaction(function () use ($booking) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $locked->update(['status' => 'canceled', 'canceled_at' => now()]);

            foreach ($locked->items as $item) {
                PropertyAvailability::query()->updateOrCreate(
                    ['property_id' => $locked->property_id, 'date' => $item->date->toDateString()],
                    ['status' => 'available'],
                );
            }

            $this->auditLogger->log('realestate.booking.canceled', $locked, []);

            rescue(fn () => Bus::dispatch(new SendTemplatedEmailJob($locked->guest_email, 'booking.canceled', [
                'checkin' => $locked->checkin_date->toDateString(),
                'checkout' => $locked->checkout_date->toDateString(),
            ], $locked->locale)), report: false);

            return $locked->fresh();
        });
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $last = Invoice::query()->where('invoice_number', 'like', "INV-{$year}-%")->latest('id')->first();
        $seq = $last ? ((int) substr($last->invoice_number, -6)) + 1 : 1;

        return sprintf('INV-%s-%06d', $year, $seq);
    }
}
