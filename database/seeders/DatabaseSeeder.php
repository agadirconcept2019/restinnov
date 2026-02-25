<?php

namespace Database\Seeders;

use Database\Seeders\Core\CoreSeeder;
use Database\Seeders\RealEstate\RealEstateSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CoreSeeder::class);
        $this->call(RealEstateSeeder::class);
    }
}
