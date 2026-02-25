<?php

namespace App\Modules\RealEstate\Jobs;

use App\Models\RealEstate\PropertyIcalFeed;
use App\Models\RealEstate\PropertySyncLog;
use App\Modules\RealEstate\Services\IcalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncIcalFeedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $feedId)
    {
    }

    public function handle(IcalService $icalService): void
    {
        $feed = PropertyIcalFeed::query()->find($this->feedId);
        if (! $feed || ! $feed->is_active) {
            return;
        }

        $log = PropertySyncLog::query()->create([
            'property_ical_feed_id' => $feed->id,
            'started_at' => now(),
            'status' => 'running',
        ]);

        try {
            $count = $icalService->sync($feed);
            $feed->update(['last_synced_at' => now(), 'last_status' => 'success', 'last_error' => null]);
            $log->update(['ended_at' => now(), 'status' => 'success', 'events_count' => $count]);
        } catch (\Throwable $e) {
            $feed->update(['last_synced_at' => now(), 'last_status' => 'fail', 'last_error' => (string) str($e->getMessage())->limit(500)]);
            $log->update(['ended_at' => now(), 'status' => 'fail', 'error_excerpt' => (string) str($e->getMessage())->limit(500)]);
        }
    }
}
