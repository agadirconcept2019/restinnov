<?php

namespace App\Modules\OwnerPortal\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyInquiry;
use App\Modules\OwnerPortal\Support\OwnerAccess;

class DashboardController extends Controller
{
    use OwnerAccess;

    public function __invoke()
    {
        $this->ensureOwnerRole();
        $ownerId = auth()->id();

        return view('ownerportal::owner.dashboard', [
            'propertiesCount' => Property::query()->where('owner_user_id', $ownerId)->count(),
            'bookingRequestsCount' => BookingRequest::query()->whereHas('property', fn ($q) => $q->where('owner_user_id', $ownerId))->count(),
            'inquiriesCount' => PropertyInquiry::query()->whereHas('property', fn ($q) => $q->where('owner_user_id', $ownerId))->count(),
        ]);
    }
}
