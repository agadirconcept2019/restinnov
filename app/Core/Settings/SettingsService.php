<?php

namespace App\Core\Settings;

use App\Models\Core\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('core.settings', 1800, function () {
            return Setting::query()->get()->mapWithKeys(function (Setting $setting) {
                return [$setting->group.'.'.$setting->key => $setting->value];
            })->toArray();
        });

        return $settings[$group.'.'.$key] ?? $default;
    }

    public function put(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'type' => $type],
        );

        Cache::forget('core.settings');
    }
}
