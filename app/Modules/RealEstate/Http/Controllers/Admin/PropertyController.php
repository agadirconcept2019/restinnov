<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Amenity;
use App\Models\RealEstate\Area;
use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use App\Modules\RealEstate\Http\Requests\Admin\StorePropertyRequest;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::query()->with(['translations','city.translations','type.translations'])->latest()->paginate(12);

        return view('realestate::admin.properties.index', compact('properties'));
    }

    public function create()
    {
        return view('realestate::admin.properties.form', $this->formData(new Property()));
    }

    public function store(StorePropertyRequest $request)
    {
        $property = Property::query()->create($this->propertyPayload($request->validated()) + ['created_by' => auth()->id(), 'updated_by' => auth()->id()]);
        $this->syncRelations($property, $request->validated());

        return redirect()->route('admin.real-estate.properties.edit', $property)->with('status', 'Property created.');
    }

    public function edit(Property $property)
    {
        $property->load(['translations','amenities','availabilities']);

        return view('realestate::admin.properties.form', $this->formData($property));
    }

    public function update(StorePropertyRequest $request, Property $property)
    {
        $property->update($this->propertyPayload($request->validated()) + ['updated_by' => auth()->id()]);
        $this->syncRelations($property, $request->validated());

        return back()->with('status', 'Property updated.');
    }

    public function destroy(Property $property)
    {
        $property->delete();

        return redirect()->route('admin.real-estate.properties.index')->with('status', 'Property deleted.');
    }

    private function formData(Property $property): array
    {
        return [
            'property' => $property,
            'types' => PropertyType::with('translations')->get(),
            'modes' => RentalMode::with('translations')->get(),
            'cities' => City::with('translations')->get(),
            'areas' => Area::with('translations')->get(),
            'amenities' => Amenity::with('translations')->get(),
        ];
    }

    private function propertyPayload(array $data): array
    {
        return collect($data)->only([
            'slug','status','property_type_id','rental_mode_id','city_id','area_id','base_price_per_night','currency','max_guests','bedrooms','beds','bathrooms','checkin_from','checkout_until','address_line','latitude','longitude','is_featured','published_at'
        ])->toArray();
    }

    private function syncRelations(Property $property, array $data): void
    {
        $property->translations()->updateOrCreate(['locale' => 'en'], [
            'title' => $data['title_en'],
            'excerpt' => $data['excerpt_en'] ?? null,
            'description' => $data['description_en'] ?? null,
            'house_rules_text' => $data['house_rules_en'] ?? null,
        ]);

        if (! empty($data['title_fr'])) {
            $property->translations()->updateOrCreate(['locale' => 'fr'], [
                'title' => $data['title_fr'],
                'excerpt' => $data['excerpt_fr'] ?? null,
                'description' => $data['description_fr'] ?? null,
                'house_rules_text' => $data['house_rules_fr'] ?? null,
            ]);
        }

        $property->amenities()->sync($data['amenity_ids'] ?? []);
    }
}
