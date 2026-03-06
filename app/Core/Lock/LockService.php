<?php

namespace App\Core\Lock;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class LockService
{
    public function runWithLock(string $key, int $seconds, Closure $callback, int $waitSeconds = 0): mixed
    {
        $lock = Cache::lock($key, $seconds);

        try {
            if ($waitSeconds > 0) {
                return $lock->block($waitSeconds, $callback);
            }

            if (! $lock->get()) {
                return null;
            }

            return $callback();
        } catch (LockTimeoutException) {
            return null;
        } finally {
            rescue(fn () => $lock->release(), report: false);
        }
    }
}

