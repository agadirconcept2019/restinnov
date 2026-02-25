<?php

namespace Database\Seeders\Core;

use App\Models\Core\Locale;
use App\Models\Core\Module;
use App\Models\Core\Setting;
use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $locales = [
            ['code' => 'en', 'name' => 'English', 'is_default' => true, 'sort_order' => 1],
            ['code' => 'fr', 'name' => 'Français', 'is_default' => false, 'sort_order' => 2],
            ['code' => 'es', 'name' => 'Español', 'is_default' => false, 'sort_order' => 3],
        ];

        foreach ($locales as $locale) {
            Locale::query()->updateOrCreate(
                ['code' => $locale['code']],
                $locale + ['is_active' => true],
            );
        }

        $settings = [
            ['group' => 'site', 'key' => 'site_name', 'value' => 'RestInnov CMS', 'type' => 'string'],
            ['group' => 'site', 'key' => 'default_locale', 'value' => 'en', 'type' => 'string'],
            ['group' => 'site', 'key' => 'support_email', 'value' => 'support@example.com', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']],
            );
        }

        foreach (['core', 'real-estate', 'cms-pages', 'blog', 'forms'] as $slug) {
            Module::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => str($slug)->replace('-', ' ')->title()->toString(),
                    'version' => '1.0.0',
                    'is_enabled' => in_array($slug, ['core', 'real-estate', 'cms-pages', 'blog', 'forms'], true),
                    'installed_at' => now(),
                    'meta' => ['source' => 'core-seeder'],
                ],
            );
        }
    }
}
