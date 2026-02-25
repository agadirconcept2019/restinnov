<?php

namespace Database\Seeders;

use Database\Seeders\Core\CoreSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CoreSeeder::class);
    }
}
