<?php

namespace App\Core\Cache;

use App\Models\Core\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsRepository
{
    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return Cache::remember("settings:$group:$key", now()->addMinutes(30), function () use ($group, $key, $default) {
            $row = Setting::query()->where('group', $group)->where('key', $key)->first();

            return $row?->value ?? $default;
        });
    }

    public function forget(string $group, string $key): void
    {
        Cache::forget("settings:$group:$key");
    }
}
