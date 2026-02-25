<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'property_slug' => $this->property?->slug,
            'status' => $this->status,
            'checkin_date' => optional($this->checkin_date)?->toDateString(),
            'checkout_date' => optional($this->checkout_date)?->toDateString(),
            'nights' => $this->nights,
            'guests' => $this->guests,
            'guest_full_name' => $this->guest_full_name,
            'guest_email_masked' => str($this->guest_email)->replaceMatches('/(^.).*(@.*$)/', '$1***$2')->toString(),
            'total' => (float) $this->total,
            'currency' => $this->currency,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
