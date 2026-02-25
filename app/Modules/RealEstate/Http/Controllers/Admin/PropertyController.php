<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Core\AdminTable\AdminTableQuery;
use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Core\UserSavedView;
use App\Models\RealEstate\Amenity;
use App\Models\RealEstate\Area;
use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use App\Models\User;
use App\Modules\RealEstate\Http\Requests\Admin\StorePropertyRequest;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(AdminTableQuery $adminTableQuery)
    {
        $query = Property::query()->with(['translations', 'city.translations', 'type.translations', 'owner']);
        $properties = $adminTableQuery->apply($query, [
            'search' => ['slug'],
            'filters' => [
                'status' => fn ($q, $v) => $q->where('status', $v),
                'city_id' => fn ($q, $v) => $q->where('city_id', $v),
                'property_type_id' => fn ($q, $v) => $q->where('property_type_id', $v),
                'owner_user_id' => fn ($q, $v) => $q->where('owner_user_id', $v),
                'featured' => fn ($q, $v) => $q->where('is_featured', $v === '1'),
            ],
            'sorts' => ['id', 'slug', 'status', 'created_at'],
            'default_sort' => 'created_at',
            'default_dir' => 'desc',
        ])->paginate(15)->withQueryString();

        return view('realestate::admin.properties.index', [
            'properties' => $properties,
            'cities' => City::query()->with('translations')->get(),
            'types' => PropertyType::query()->with('translations')->get(),
            'owners' => User::query()->where('role', 'owner')->orderBy('name')->get(),
            'savedViews' => UserSavedView::query()->where('user_id', auth()->id())->where('resource_key', 'properties')->get(),
        ]);
    }

    public function bulk(Request $request, AuditLogger $auditLogger)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:properties,id'],
            'action' => ['required', 'in:publish,archive,assign_owner'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $q = Property::query()->whereIn('id', $data['ids']);
        if ($data['action'] === 'publish') {
            $q->update(['status' => 'published', 'published_at' => now(), 'updated_by' => auth()->id()]);
        } elseif ($data['action'] === 'archive') {
            $q->update(['status' => 'draft', 'updated_by' => auth()->id()]);
        } else {
            $q->update(['owner_user_id' => $data['owner_user_id'], 'updated_by' => auth()->id()]);
        }

        $auditLogger->log('realestate.properties.bulk', null, [
            'action' => $data['action'],
            'count' => count($data['ids']),
        ]);

        return back()->with('status', 'Bulk action applied.');
    }

    public function create()
    {
        return view('realestate::admin.properties.form', $this->formData(new Property()));
    }

    public function store(StorePropertyRequest $request, AuditLogger $auditLogger)
    {
        $property = Property::query()->create($this->propertyPayload($request->validated()) + ['created_by' => auth()->id(), 'updated_by' => auth()->id()]);
        $this->syncRelations($property, $request->validated());
        $auditLogger->log($property->status === 'published' ? 'property.published' : 'property.created', $property, ['slug' => $property->slug]);

        return redirect()->route('admin.real-estate.properties.edit', $property)->with('status', 'Property created.');
    }

    public function edit(Property $property)
    {
        $property->load(['translations', 'amenities', 'availabilities']);

        return view('realestate::admin.properties.form', $this->formData($property));
    }

    public function update(StorePropertyRequest $request, Property $property, AuditLogger $auditLogger)
    {
        $beforeOwner = $property->owner_user_id;
        $property->update($this->propertyPayload($request->validated()) + ['updated_by' => auth()->id()]);
        $this->syncRelations($property, $request->validated());
        $auditLogger->log($property->status === 'published' ? 'property.published' : 'property.updated', $property, ['slug' => $property->slug]);
        if ((int) $beforeOwner !== (int) $property->owner_user_id) {
            $auditLogger->log('property.owner_assigned', $property, ['owner_user_id' => $property->owner_user_id]);
        }

        return back()->with('status', 'Property updated.');
    }

    public function destroy(Property $property, AuditLogger $auditLogger)
    {
        $auditLogger->log('property.deleted', $property, ['slug' => $property->slug]);
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
            'owners' => User::query()->where('role', 'owner')->orderBy('name')->get(),
        ];
    }

    private function propertyPayload(array $data): array
    {
        return collect($data)->only([
            'slug', 'status', 'property_type_id', 'rental_mode_id', 'city_id', 'area_id', 'base_price_per_night', 'currency', 'max_guests', 'bedrooms', 'beds', 'bathrooms', 'checkin_from', 'checkout_until', 'address_line', 'latitude', 'longitude', 'is_featured', 'published_at', 'owner_user_id',
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
