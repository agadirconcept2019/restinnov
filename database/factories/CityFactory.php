<?php

namespace Database\Factories;

use App\Models\RealEstate\City;
use App\Models\RealEstate\CityTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'slug' => Str::slug(fake()->city().'-'.fake()->unique()->numberBetween(1,999)),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (City $city) {
            CityTranslation::query()->create([
                'city_id' => $city->id,
                'locale' => 'en',
                'name' => fake()->city(),
            ]);
        });
    }
}
