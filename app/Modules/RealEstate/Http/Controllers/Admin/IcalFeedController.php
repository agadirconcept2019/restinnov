<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyIcalFeed;
use App\Modules\RealEstate\Http\Requests\Admin\StoreIcalFeedRequest;
use App\Modules\RealEstate\Jobs\SyncIcalFeedJob;

class IcalFeedController extends Controller
{
    public function index()
    {
        $feeds = PropertyIcalFeed::query()->with('property.translations')->latest()->paginate(20);
        $properties = Property::query()->with('translations')->orderBy('id')->get();

        return view('realestate::admin.ical-feeds.index', compact('feeds', 'properties'));
    }

    public function store(StoreIcalFeedRequest $request, AuditLogger $auditLogger)
    {
        $feed = PropertyIcalFeed::query()->create($request->validated() + ['is_active' => (bool) $request->boolean('is_active', true)]);
        $auditLogger->log('realestate.ical.feed_created', $feed, ['property_id' => $feed->property_id]);

        return back()->with('status', 'iCal feed created.');
    }

    public function update(StoreIcalFeedRequest $request, PropertyIcalFeed $icalFeed, AuditLogger $auditLogger)
    {
        $icalFeed->update($request->validated() + ['is_active' => (bool) $request->boolean('is_active', true)]);
        $auditLogger->log('realestate.ical.feed_updated', $icalFeed, ['property_id' => $icalFeed->property_id]);

        return back()->with('status', 'iCal feed updated.');
    }

    public function destroy(PropertyIcalFeed $icalFeed, AuditLogger $auditLogger)
    {
        $auditLogger->log('realestate.ical.feed_deleted', $icalFeed, ['property_id' => $icalFeed->property_id]);
        $icalFeed->delete();

        return back()->with('status', 'iCal feed deleted.');
    }

    public function syncNow(PropertyIcalFeed $icalFeed)
    {
        dispatch(new SyncIcalFeedJob($icalFeed->id));

        return back()->with('status', 'iCal sync job dispatched.');
    }
}
