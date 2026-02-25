<?php

namespace App\Modules\RealEstate\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Amenity;
use App\Models\RealEstate\Area;
use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use App\Modules\RealEstate\Services\PropertySearchServiceV2;

class PropertyPublicController extends Controller
{
    public function index(PropertySearchServiceV2 $searchService)
    {
        $filters = request()->only(['city', 'area', 'type', 'mode', 'guests', 'guests_min', 'price_min', 'price_max', 'checkin', 'checkout', 'amenities', 'sort']);
        $properties = $searchService->search($filters);

        $taxonomies = [
            'cities' => City::with('translations')->where('is_active', true)->get(),
            'areas' => Area::with('translations')->where('is_active', true)->get(),
            'types' => PropertyType::with('translations')->where('is_active', true)->get(),
            'modes' => RentalMode::with('translations')->where('is_active', true)->get(),
            'amenities' => Amenity::with('translations')->where('is_active', true)->get(),
        ];

        return view('realestate::public.properties.index', compact('properties', 'filters', 'taxonomies'));
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

    public function type(string $typeSlug, PropertySearchServiceV2 $searchService)
    {
        request()->merge(['type' => $typeSlug]);
        return $this->index($searchService);
    }

    public function mode(string $modeSlug, PropertySearchServiceV2 $searchService)
    {
        request()->merge(['mode' => $modeSlug]);
        return $this->index($searchService);
    }

    public function city(string $citySlug, PropertySearchServiceV2 $searchService)
    {
        request()->merge(['city' => $citySlug]);
        return $this->index($searchService);
    }

    public function area(string $areaSlug, PropertySearchServiceV2 $searchService)
    {
        request()->merge(['area' => $areaSlug]);
        return $this->index($searchService);
    }
}
