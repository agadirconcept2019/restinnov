<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'property_slug' => $this->property?->slug,
            'status' => $this->status,
            'checkin_date' => optional($this->checkin_date)?->toDateString(),
            'checkout_date' => optional($this->checkout_date)?->toDateString(),
            'guests' => $this->guests,
            'full_name' => $this->full_name,
            'email_masked' => str($this->email)->replaceMatches('/(^.).*(@.*$)/', '$1***$2')->toString(),
            'estimated_total' => (float) $this->estimated_total,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
