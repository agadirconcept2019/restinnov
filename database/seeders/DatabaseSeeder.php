<?php

namespace Database\Seeders;

use App\Models\RealEstate\City;
use App\Models\RealEstate\Property;
use Illuminate\Database\Seeder;
use Database\Seeders\Core\CoreSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CoreSeeder::class);

        if (app()->environment('local', 'testing')) {
            City::factory()->count(3)->create();
            Property::factory()->count(6)->create();
            Property::factory()->draft()->count(2)->create();
        }
    }
}
