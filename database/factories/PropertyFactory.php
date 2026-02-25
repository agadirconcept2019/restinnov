<?php

namespace Database\Factories;

use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $title = fake()->streetName().' Villa';

        return [
            'slug' => Str::slug($title.'-'.fake()->unique()->numberBetween(1, 10000)),
            'title' => $title,
            'status' => 'published',
            'city_id' => City::factory(),
            'base_price_per_night' => fake()->numberBetween(90, 500),
            'currency' => 'EUR',
            'max_guests' => fake()->numberBetween(2, 10),
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 4),
            'published_at' => now(),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
    }
}
