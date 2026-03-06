<?php

namespace App\Modules\RealEstate\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Modules\RealEstate\Services\IcalService;

class PropertyIcsController extends Controller
{
    public function show(string $slug, IcalService $icalService)
    {
        $property = Property::query()->published()->where('slug', $slug)->firstOrFail();

        return response($icalService->exportIcs($property->id), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="'.$property->slug.'.ics"',
        ]);
    }
}
