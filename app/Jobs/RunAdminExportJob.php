<?php

namespace App\Jobs;

use App\Core\AdminTable\ExportRegistry;
use App\Models\Core\ExportRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class RunAdminExportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $exportRunId)
    {
    }

    public function handle(ExportRegistry $registry): void
    {
        $run = ExportRun::query()->find($this->exportRunId);
        if (! $run) {
            return;
        }

        $run->update(['status' => 'processing']);

        try {
            $dataset = $registry->dataset($run->resource_key);
            $rows = $dataset['query']->orderBy('id')->get($dataset['columns']);
            $filename = 'exports/'.$run->resource_key.'-'.$run->id.'-'.now()->format('YmdHis').'.csv';

            $stream = fopen('php://temp', 'w+');
            fputcsv($stream, $dataset['columns']);
            foreach ($rows as $row) {
                fputcsv($stream, collect($dataset['columns'])->map(fn ($col) => data_get($row, $col))->all());
            }
            rewind($stream);
            Storage::disk('local')->put($filename, stream_get_contents($stream));
            fclose($stream);

            $run->update([
                'status' => 'completed',
                'file_path' => $filename,
                'rows_count' => $rows->count(),
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'error_excerpt' => str($e->getMessage())->limit(250),
                'completed_at' => now(),
            ]);
        }
    }
}
