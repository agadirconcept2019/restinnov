<?php

namespace Database\Factories;

use App\Models\RealEstate\RentalMode;
use App\Models\RealEstate\RentalModeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RentalModeFactory extends Factory
{
    protected $model = RentalMode::class;

    public function definition(): array
    {
        return ['slug' => Str::slug(fake()->word().'-'.fake()->unique()->numberBetween(1,999)), 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (RentalMode $mode) {
            RentalModeTranslation::query()->create(['rental_mode_id'=>$mode->id,'locale'=>'en','name'=>ucfirst(fake()->word())]);
        });
    }
}
