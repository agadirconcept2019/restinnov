<?php

namespace Database\Seeders\Core;

use App\Models\Core\EmailTemplate;
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
            ['group' => 'forms', 'key' => 'captcha_enabled', 'value' => false, 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']],
            );
        }


        foreach ([
            ['key' => 'booking.confirmed', 'locale' => 'en', 'subject' => 'Booking confirmed {invoice_number}', 'body_html' => '<p>Your booking for {property_title} is confirmed from {checkin} to {checkout}. Total: {total}.</p>'],
            ['key' => 'booking.canceled', 'locale' => 'en', 'subject' => 'Booking canceled', 'body_html' => '<p>Your booking from {checkin} to {checkout} has been canceled.</p>'],
        ] as $template) {
            EmailTemplate::query()->updateOrCreate(
                ['key' => $template['key'], 'locale' => $template['locale']],
                ['subject' => $template['subject'], 'body_html' => $template['body_html'], 'is_active' => true],
            );
        }

        foreach (['core', 'real-estate', 'cms-pages', 'blog', 'forms', 'owner-portal', 'migration-tools', 'communications'] as $slug) {
            Module::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => str($slug)->replace('-', ' ')->title()->toString(),
                    'version' => '1.0.0',
                    'is_enabled' => in_array($slug, ['core', 'real-estate', 'cms-pages', 'blog', 'forms', 'owner-portal', 'migration-tools', 'communications'], true),
                    'installed_at' => now(),
                    'meta' => ['source' => 'core-seeder'],
                ],
            );
        }
    }
}
