<?php

namespace App\Modules\RealEstate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\Amenity;
use App\Models\RealEstate\Area;
use App\Models\RealEstate\City;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaxonomyController extends Controller
{
    private array $map = [
        'property-types' => [PropertyType::class, 'property_type_id'],
        'rental-modes' => [RentalMode::class, 'rental_mode_id'],
        'cities' => [City::class, 'city_id'],
        'areas' => [Area::class, 'area_id'],
        'amenities' => [Amenity::class, 'amenity_id'],
    ];

    public function index(string $taxonomy)
    {
        [$model] = $this->resolve($taxonomy);
        $items = $model::query()->with('translations')->paginate(20);

        return view('realestate::admin.taxonomies.index', compact('items', 'taxonomy'));
    }

    public function store(Request $request, string $taxonomy)
    {
        [$model, $translationFk] = $this->resolve($taxonomy);

        $data = $request->validate([
            'slug' => ['nullable','string','max:180'],
            'name_en' => ['required','string','max:180'],
            'name_fr' => ['nullable','string','max:180'],
            'city_id' => ['nullable','exists:cities,id'],
        ]);

        $item = $model::query()->create([
            'slug' => $data['slug'] ?: Str::slug($data['name_en']),
            'city_id' => $data['city_id'] ?? null,
            'is_active' => true,
        ]);

        $item->translations()->create(['locale' => 'en', 'name' => $data['name_en'], $translationFk => $item->id]);
        if (! empty($data['name_fr'])) {
            $item->translations()->create(['locale' => 'fr', 'name' => $data['name_fr'], $translationFk => $item->id]);
        }

        return back()->with('status', 'Created');
    }

    private function resolve(string $taxonomy): array
    {
        abort_unless(isset($this->map[$taxonomy]), 404);

        return $this->map[$taxonomy];
    }
}
