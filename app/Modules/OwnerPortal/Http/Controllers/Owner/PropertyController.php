<?php

namespace App\Modules\OwnerPortal\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Core\Audit\AuditLogger;
use App\Models\RealEstate\Property;
use App\Modules\RealEstate\Http\Requests\Admin\BulkAvailabilityRequest;
use App\Modules\RealEstate\Services\AvailabilityServiceV2;
use App\Modules\OwnerPortal\Support\OwnerAccess;

class PropertyController extends Controller
{
    use OwnerAccess;

    public function index()
    {
        $this->ensureOwnerRole();
        $properties = Property::query()->with(['translations', 'city.translations'])->where('owner_user_id', auth()->id())->paginate(12);

        return view('ownerportal::owner.properties.index', compact('properties'));
    }

    public function show(Property $property)
    {
        $this->ensureOwnsProperty($property);
        $property->load(['translations', 'availabilities']);

        return view('ownerportal::owner.properties.show', compact('property'));
    }

    public function calendar(Property $property)
    {
        $this->ensureOwnsProperty($property);
        $month = request('month', now()->format('Y-m'));
        $rows = $property->availabilities()->whereBetween('date', [$month.'-01', date('Y-m-t', strtotime($month.'-01'))])->orderBy('date')->get();

        return view('ownerportal::owner.properties.calendar', compact('property', 'month', 'rows'));
    }

    public function bulkUpdate(BulkAvailabilityRequest $request, Property $property, AvailabilityServiceV2 $service, AuditLogger $auditLogger)
    {
        $this->ensureOwnsProperty($property);
        $service->bulkUpdate($property->id, $request->string('from_date')->value(), $request->string('to_date')->value(), $request->validated());
        $auditLogger->log('owner.calendar.bulk_update', $property, ['from' => $request->string('from_date')->value(), 'to' => $request->string('to_date')->value()]);

        return back()->with('status', 'Calendar updated.');
    }
}
