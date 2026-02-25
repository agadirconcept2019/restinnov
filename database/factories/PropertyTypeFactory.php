<?php

namespace Database\Factories;

use App\Models\RealEstate\PropertyType;
use App\Models\RealEstate\PropertyTypeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyTypeFactory extends Factory
{
    protected $model = PropertyType::class;

    public function definition(): array
    {
        return ['slug' => Str::slug(fake()->word().'-'.fake()->unique()->numberBetween(1,999)), 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PropertyType $type) {
            PropertyTypeTranslation::query()->create(['property_type_id'=>$type->id,'locale'=>'en','name'=>ucfirst(fake()->word())]);
        });
    }
}
