<?php

namespace Database\Factories;

use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyTranslation;
use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\RentalMode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'slug' => Str::slug(fake()->streetName().'-'.fake()->unique()->numberBetween(1,9999)),
            'status' => 'published',
            'property_type_id' => PropertyType::factory(),
            'rental_mode_id' => RentalMode::factory(),
            'city_id' => City::factory(),
            'base_price_per_night' => fake()->numberBetween(400, 3000),
            'currency' => 'MAD',
            'max_guests' => fake()->numberBetween(2, 8),
            'bedrooms' => fake()->numberBetween(1, 4),
            'beds' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 3),
            'published_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Property $property) {
            PropertyTranslation::query()->create([
                'property_id' => $property->id,
                'locale' => 'en',
                'title' => fake()->streetName().' Villa',
                'description' => fake()->paragraph(),
            ]);
        });
    }

    public function draft(): self
    {
        return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
    }
}
