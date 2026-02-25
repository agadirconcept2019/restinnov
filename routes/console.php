<?php

use App\Core\Lock\LockService;
use App\Models\CmsPages\Page;
use App\Models\RealEstate\Property;
use App\Modules\Blog\Models\Post;
use App\Modules\MigrationTools\Jobs\ProcessMigrationRunJob;
use App\Modules\MigrationTools\Models\MigrationRun;
use App\Models\RealEstate\PropertyIcalFeed;
use App\Modules\RealEstate\Jobs\SyncIcalFeedJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('realestate:sync-ical', function (LockService $lockService) {
    $result = $lockService->runWithLock('realestate:ical:dispatch', 600, function () {
        $count = 0;
        PropertyIcalFeed::query()->where('is_active', true)->chunkById(100, function ($feeds) use (&$count) {
            foreach ($feeds as $feed) {
                dispatch(new SyncIcalFeedJob($feed->id));
                $count++;
            }
        });

        return $count;
    });

    if ($result === null) {
        $this->warn('iCal sync dispatch skipped: another dispatch is already running.');

        return;
    }

    $this->info("Dispatched {$result} iCal sync jobs.");
})->purpose('Dispatch iCal sync jobs for active RealEstate feeds');

Artisan::command('migration:run {runId}', function (int $runId) {
    dispatch(new ProcessMigrationRunJob($runId));
    $this->info('Migration run dispatched.');
})->purpose('Dispatch a migration run by ID');

Artisan::command('migration:export {entity} {--format=json}', function (string $entity) {
    $format = strtolower((string) $this->option('format'));
    $data = match ($entity) {
        'pages' => Page::query()->with('translations')->get()->toArray(),
        'posts' => Post::query()->with('translations')->get()->toArray(),
        'properties' => Property::query()->with('translations')->get()->toArray(),
        default => throw new InvalidArgumentException('Unsupported entity'),
    };

    $path = storage_path('app/exports/'.$entity.'-'.now()->format('YmdHis').'.'.($format === 'csv' ? 'csv' : 'json'));
    if ($format === 'csv') {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        $fp = fopen($path, 'w');
        $first = (array) ($data[0] ?? []);
        fputcsv($fp, array_keys($first));
        foreach ($data as $row) {
            fputcsv($fp, array_map(fn ($v) => is_scalar($v) || $v === null ? $v : json_encode($v), (array) $row));
        }
        fclose($fp);
    } else {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    $this->info('Export created: '.$path);
})->purpose('Export entities to CSV/JSON');

Schedule::command('realestate:sync-ical')->everyThirtyMinutes();
