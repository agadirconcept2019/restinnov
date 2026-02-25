<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Property;
use App\Services\RealEstate\PropertySearchService;

class PropertyController extends Controller
{
    public function index(PropertySearchService $searchService)
    {
        $properties = $searchService->search(request()->only(['city', 'guests']));

        return view('public.properties.index', compact('properties'));
    }

    public function show(string $slug)
    {
        $property = Property::query()->with('city')->published()->where('slug', $slug)->firstOrFail();

        return view('public.properties.show', compact('property'));
    }

    public function city(string $citySlug, PropertySearchService $searchService)
    {
        $properties = $searchService->search(['city' => $citySlug]);

        return view('public.properties.index', compact('properties'));
    }
}
