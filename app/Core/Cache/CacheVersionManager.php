<?php

namespace App\Core\Cache;

use Illuminate\Support\Facades\Cache;

class CacheVersionManager
{
    public function versionedKey(string $scope, ?string $locale, string $suffix): string
    {
        $localePart = $locale ?: 'global';
        $version = (int) Cache::get($this->versionKey($scope), 1);

        return sprintf('%s:%s:v%d:%s', $scope, $localePart, $version, $suffix);
    }

    public function bump(string $scope): void
    {
        $key = $this->versionKey($scope);
        if (! Cache::has($key)) {
            Cache::forever($key, 1);
        }

        Cache::increment($key);
    }

    private function versionKey(string $scope): string
    {
        return 'cache:version:'.$scope;
    }
}

