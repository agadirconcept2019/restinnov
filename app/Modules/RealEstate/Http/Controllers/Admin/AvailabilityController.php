<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Modules\RealEstate\Http\Requests\Admin\BulkAvailabilityRequest;
use App\Modules\RealEstate\Services\AvailabilityServiceV2;

class AvailabilityController extends Controller
{
    public function index()
    {
        $properties = Property::query()->with('translations')->orderBy('id')->get();
        $propertyId = (int) request('property_id', $properties->first()?->id);
        $month = request('month', now()->format('Y-m'));
        $from = $month.'-01';
        $to = date('Y-m-t', strtotime($from));

        $rows = [];
        if ($propertyId) {
            $items = Property::findOrFail($propertyId)->availabilities()->whereBetween('date', [$from, $to])->orderBy('date')->get();
            $rows = $items->groupBy('date');
        }

        return view('realestate::admin.availability.index', compact('properties', 'propertyId', 'month', 'rows'));
    }

    public function bulkUpdate(BulkAvailabilityRequest $request, AvailabilityServiceV2 $service, AuditLogger $auditLogger)
    {
        try {
            $updated = $service->bulkUpdate(
                (int) $request->integer('property_id'),
                $request->string('from_date'),
                $request->string('to_date'),
                $request->validated(),
            );
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            return back()->withErrors(['from_date' => $exception->getMessage()])->withInput();
        }

        $auditLogger->log('realestate.availability.bulk_update', null, ['property_id' => $request->integer('property_id'), 'updated_days' => $updated]);

        return back()->with('status', "Availability updated for {$updated} days.");
    }
}
