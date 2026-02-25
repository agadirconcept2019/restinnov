<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RealEstate\PropertyIcalFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class OpsController extends Controller
{
    public function health()
    {
        $dbOk = true;

        try {
            DB::select('select 1');
        } catch (\Throwable) {
            $dbOk = false;
        }

        $failedJobsCount = DB::table('failed_jobs')->count();
        $icalFailureCount = PropertyIcalFeed::query()->where('last_status', 'fail')->count();

        return view('admin.ops.health', [
            'dbOk' => $dbOk,
            'cacheDriver' => config('cache.default'),
            'queueDriver' => config('queue.default'),
            'failedJobsCount' => $failedJobsCount,
            'icalFailureCount' => $icalFailureCount,
        ]);
    }

    public function jobs()
    {
        $failedJobs = DB::table('failed_jobs')->latest('id')->paginate(20);

        return view('admin.ops.jobs', compact('failedJobs'));
    }

    public function retryJob(Request $request)
    {
        $data = $request->validate([
            'job_id' => ['required', 'integer'],
        ]);

        Artisan::call('queue:retry', ['id' => [$data['job_id']]]);

        return back()->with('status', 'Job retry requested.');
    }
}
