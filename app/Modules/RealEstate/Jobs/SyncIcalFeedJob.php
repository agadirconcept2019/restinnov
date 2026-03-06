<?php

namespace App\Modules\RealEstate\Jobs;

use App\Core\Lock\LockService;
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

    public function handle(IcalService $icalService, ?LockService $lockService = null): void
    {
        $lockService ??= app(LockService::class);
        $feed = PropertyIcalFeed::query()->find($this->feedId);
        if (! $feed || ! $feed->is_active) {
            return;
        }

        $lockResult = $lockService->runWithLock('realestate:ical:global', 300, function () use ($lockService, $feed, $icalService) {
            return $lockService->runWithLock('realestate:ical:feed:'.$feed->id, 180, function () use ($feed, $icalService) {
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
                    $excerpt = (string) str($e->getMessage())->limit(500);
                    $feed->update(['last_synced_at' => now(), 'last_status' => 'fail', 'last_error' => $excerpt]);
                    $log->update(['ended_at' => now(), 'status' => 'fail', 'error_excerpt' => $excerpt]);
                }

                return true;
            });
        });

        if ($lockResult === null) {
            return;
        }
    }
}
