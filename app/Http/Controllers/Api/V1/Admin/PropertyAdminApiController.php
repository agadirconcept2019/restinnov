<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PropertyResource;
use App\Models\RealEstate\Property;
use Illuminate\Http\Request;

class PropertyAdminApiController extends ApiController
{
    public function index()
    {
        $this->resolveLocale();

        $properties = Property::query()
            ->with(['translations', 'city.translations', 'type.translations'])
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->paginate((int) request('per_page', 20))
            ->withQueryString();

        return PropertyResource::collection($properties);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:180', 'unique:properties,slug'],
            'status' => ['required', 'in:draft,published'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'rental_mode_id' => ['required', 'exists:rental_modes,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'base_price_per_night' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'max_guests' => ['required', 'integer', 'min:1'],
        ]);

        $property = Property::query()->create($data + ['created_by' => auth()->id(), 'updated_by' => auth()->id()]);

        return PropertyResource::make($property->load(['translations', 'city.translations', 'type.translations']))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Property $property)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:draft,published'],
            'base_price_per_night' => ['nullable', 'numeric', 'min:0'],
            'max_guests' => ['nullable', 'integer', 'min:1'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $property->update($data + ['updated_by' => auth()->id()]);

        return PropertyResource::make($property->fresh()->load(['translations', 'city.translations', 'type.translations']));
    }
}
