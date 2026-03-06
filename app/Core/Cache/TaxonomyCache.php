<?php

namespace App\Core\Cache;

use Closure;
use Illuminate\Support\Facades\Cache;

class TaxonomyCache
{
    public function remember(string $key, Closure $callback, int $minutes = 30): mixed
    {
        return Cache::remember("taxonomy:$key", now()->addMinutes($minutes), $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget("taxonomy:$key");
    }
}
