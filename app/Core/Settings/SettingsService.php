<?php

namespace App\Core\Settings;

use App\Models\Core\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('core.settings', 3600, fn () => Setting::query()->pluck('value', 'key')->toArray());

        return $settings[$key] ?? $default;
    }

    public function put(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget('core.settings');
    }
}
