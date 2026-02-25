<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke()
    {
        $dbOk = false;

        try {
            DB::select('select 1');
            $dbOk = true;
        } catch (\Throwable) {
            $dbOk = false;
        }

        return response()->json([
            'ok' => $dbOk,
            'db' => $dbOk,
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'app_env' => config('app.env'),
            'cached' => [
                'config' => app()->configurationIsCached(),
                'routes' => app()->routesAreCached(),
            ],
            'timestamp' => now()->toIso8601String(),
        ], $dbOk ? 200 : 503);
    }
}
