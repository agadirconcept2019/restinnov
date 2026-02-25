<?php

namespace App\Core\Cache;

use App\Models\Core\Menu;
use Illuminate\Support\Facades\Cache;

class MenuRepository
{
    public function getBySlug(string $slug)
    {
        return Cache::remember("menu:$slug", now()->addMinutes(30), function () use ($slug) {
            return Menu::query()->with('items')->where('slug', $slug)->first();
        });
    }

    public function forget(string $slug): void
    {
        Cache::forget("menu:$slug");
    }
}
