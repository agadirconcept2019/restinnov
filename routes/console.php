<?php

use App\Models\RealEstate\PropertyIcalFeed;
use App\Modules\RealEstate\Jobs\SyncIcalFeedJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('realestate:sync-ical', function () {
    $count = 0;
    PropertyIcalFeed::query()->where('is_active', true)->chunkById(100, function ($feeds) use (&$count) {
        foreach ($feeds as $feed) {
            dispatch(new SyncIcalFeedJob($feed->id));
            $count++;
        }
    });

    $this->info("Dispatched {$count} iCal sync jobs.");
})->purpose('Dispatch iCal sync jobs for active RealEstate feeds');

Schedule::command('realestate:sync-ical')->everyThirtyMinutes();
