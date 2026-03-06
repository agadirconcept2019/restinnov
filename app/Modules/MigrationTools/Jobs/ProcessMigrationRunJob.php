<?php

namespace App\Modules\MigrationTools\Jobs;

use App\Modules\MigrationTools\Models\MigrationRun;
use App\Modules\MigrationTools\Services\MigrationManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMigrationRunJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $runId)
    {
    }

    public function handle(MigrationManager $migrationManager): void
    {
        $run = MigrationRun::query()->find($this->runId);
        if (! $run) {
            return;
        }

        $migrationManager->processRun($run);
    }
}
