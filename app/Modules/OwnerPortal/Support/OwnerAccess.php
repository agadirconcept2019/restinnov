<?php

namespace App\Modules\OwnerPortal\Support;

use App\Models\RealEstate\Property;

trait OwnerAccess
{
    private function ensureOwnerRole(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'owner', 403);
    }

    private function ensureOwnsProperty(Property $property): void
    {
        $this->ensureOwnerRole();
        abort_unless((int) $property->owner_user_id === (int) auth()->id(), 403);
    }
}
