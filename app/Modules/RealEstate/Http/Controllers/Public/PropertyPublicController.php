<?php

namespace App\Modules\RealEstate\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Services\RealEstate\PropertySearchService;

class PropertyPublicController extends Controller
{
    public function index(PropertySearchService $searchService)
    {
        $properties = $searchService->paginated(request()->only('guests'));

        return view('realestate::public.properties.index', compact('properties'));
    }

    public function show(string $slug)
    {
        $property = Property::query()
            ->with(['translations','type.translations','mode.translations','city.translations','area.translations','amenities.translations','images.media'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $availability = $property->availabilities()->whereBetween('date', [now()->toDateString(), now()->addDays(90)->toDateString()])->orderBy('date')->get();

        return view('realestate::public.properties.show', compact('property', 'availability'));
    }

    public function type(string $typeSlug, PropertySearchService $searchService)
    {
        $properties = $searchService->paginated(['type' => $typeSlug]);

        return view('realestate::public.properties.index', compact('properties'));
    }

    public function mode(string $modeSlug, PropertySearchService $searchService)
    {
        $properties = $searchService->paginated(['mode' => $modeSlug]);

        return view('realestate::public.properties.index', compact('properties'));
    }

    public function city(string $citySlug, PropertySearchService $searchService)
    {
        $properties = $searchService->paginated(['city' => $citySlug]);

        return view('realestate::public.properties.index', compact('properties'));
    }

    public function area(string $areaSlug, PropertySearchService $searchService)
    {
        $properties = $searchService->paginated(['area' => $areaSlug]);

        return view('realestate::public.properties.index', compact('properties'));
    }
}
