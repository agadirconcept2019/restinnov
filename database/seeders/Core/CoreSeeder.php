<?php

namespace Database\Seeders\Core;

use App\Models\Core\Locale;
use App\Models\Core\Setting;
use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['code' => 'en', 'name' => 'English', 'is_default' => true],
            ['code' => 'fr', 'name' => 'Français', 'is_default' => false],
            ['code' => 'es', 'name' => 'Español', 'is_default' => false],
        ] as $locale) {
            Locale::query()->updateOrCreate(['code' => $locale['code']], $locale + ['is_enabled' => true]);
        }

        Setting::query()->updateOrCreate(['key' => 'site_name'], ['group' => 'general', 'value' => 'Rest Innov CMS']);
    }
}
