<?php

namespace Database\Seeders\RealEstate;

use App\Models\RealEstate\Amenity;
use App\Models\RealEstate\Area;
use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use Illuminate\Database\Seeder;

class RealEstateSeeder extends Seeder
{
    public function run(): void
    {
        $typeMap = [];
        foreach (['apartments' => 'Apartments', 'villas' => 'Villas', 'riads' => 'Riads'] as $slug => $name) {
            $type = PropertyType::query()->firstOrCreate(['slug' => $slug], ['is_active' => true]);
            $type->translations()->updateOrCreate(['locale' => 'en'], ['name' => $name]);
            $type->translations()->updateOrCreate(['locale' => 'fr'], ['name' => $name]);
            $typeMap[$slug] = $type;
        }

        $mode = RentalMode::query()->firstOrCreate(['slug' => 'entire-home'], ['is_active' => true]);
        $mode->translations()->updateOrCreate(['locale' => 'en'], ['name' => 'Entire home']);
        $mode->translations()->updateOrCreate(['locale' => 'fr'], ['name' => 'Logement entier']);

        $cityMap = [];
        foreach (['agadir' => 'Agadir', 'aourir' => 'Aourir', 'marrakech' => 'Marrakech'] as $slug => $name) {
            $city = City::query()->firstOrCreate(['slug' => $slug], ['is_active' => true]);
            $city->translations()->updateOrCreate(['locale' => 'en'], ['name' => $name]);
            $city->translations()->updateOrCreate(['locale' => 'fr'], ['name' => $name]);
            $cityMap[$slug] = $city;
        }

        $areas = [
            ['city' => 'agadir', 'slug' => 'marina', 'en' => 'Marina', 'fr' => 'Marina'],
            ['city' => 'aourir', 'slug' => 'tamraght', 'en' => 'Tamraght', 'fr' => 'Tamraght'],
            ['city' => 'marrakech', 'slug' => 'guéliz', 'en' => 'Gueliz', 'fr' => 'Guéliz'],
        ];
        $areaMap = [];
        foreach ($areas as $areaData) {
            $area = Area::query()->firstOrCreate(['city_id' => $cityMap[$areaData['city']]->id, 'slug' => $areaData['slug']], ['is_active' => true]);
            $area->translations()->updateOrCreate(['locale' => 'en'], ['name' => $areaData['en']]);
            $area->translations()->updateOrCreate(['locale' => 'fr'], ['name' => $areaData['fr']]);
            $areaMap[$areaData['slug']] = $area;
        }

        $amenityMap = [];
        foreach (['wifi' => 'Wi-Fi', 'parking' => 'Parking', 'pool' => 'Pool'] as $slug => $name) {
            $amenity = Amenity::query()->firstOrCreate(['slug' => $slug], ['is_active' => true]);
            $amenity->translations()->updateOrCreate(['locale' => 'en'], ['name' => $name]);
            $amenity->translations()->updateOrCreate(['locale' => 'fr'], ['name' => $name]);
            $amenityMap[$slug] = $amenity;
        }

        $samples = [
            ['slug' => 'agadir-marina-villa', 'city' => 'agadir', 'area' => 'marina', 'type' => 'villas', 'price' => 1800],
            ['slug' => 'tamraght-surf-apartment', 'city' => 'aourir', 'area' => 'tamraght', 'type' => 'apartments', 'price' => 950],
            ['slug' => 'marrakech-riad-zen', 'city' => 'marrakech', 'area' => 'guéliz', 'type' => 'riads', 'price' => 2200],
        ];

        foreach ($samples as $sample) {
            $property = Property::query()->updateOrCreate(
                ['slug' => $sample['slug']],
                [
                    'status' => 'published',
                    'property_type_id' => $typeMap[$sample['type']]->id,
                    'rental_mode_id' => $mode->id,
                    'city_id' => $cityMap[$sample['city']]->id,
                    'area_id' => $areaMap[$sample['area']]->id,
                    'base_price_per_night' => $sample['price'],
                    'currency' => 'MAD',
                    'max_guests' => 6,
                    'bedrooms' => 3,
                    'beds' => 4,
                    'bathrooms' => 2,
                    'checkin_from' => '15:00:00',
                    'checkout_until' => '11:00:00',
                    'published_at' => now(),
                ],
            );

            $property->translations()->updateOrCreate(['locale' => 'en'], [
                'title' => str($sample['slug'])->replace('-', ' ')->title()->toString(),
                'description' => 'Comfortable stay with professional management.',
                'house_rules_text' => 'No smoking. No parties.',
            ]);
            $property->translations()->updateOrCreate(['locale' => 'fr'], [
                'title' => 'Propriété '.str($sample['slug'])->replace('-', ' ')->title()->toString(),
                'description' => 'Séjour confortable avec gestion professionnelle.',
                'house_rules_text' => 'Interdiction de fumer. Pas de fêtes.',
            ]);

            $property->amenities()->sync(array_values(array_map(fn ($a) => $a->id, $amenityMap)));

            for ($i = 0; $i < 60; $i++) {
                $property->availabilities()->updateOrCreate(
                    ['date' => now()->addDays($i)->toDateString()],
                    ['status' => $i % 9 === 0 ? 'blocked' : 'available'],
                );
            }

            $property->inquiries()->create([
                'first_name' => 'Demo',
                'last_name' => 'Guest',
                'email' => 'guest@example.com',
                'message' => 'I would like more details.',
                'status' => 'new',
                'ip_hash' => hash('sha256', '127.0.0.1'),
            ]);
        }
    }
}
