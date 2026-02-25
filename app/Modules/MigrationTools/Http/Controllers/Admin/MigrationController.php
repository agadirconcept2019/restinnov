<?php

namespace App\Modules\MigrationTools\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\MigrationTools\Jobs\ProcessMigrationRunJob;
use App\Modules\MigrationTools\Models\MigrationRun;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function index()
    {
        $runs = MigrationRun::query()->latest()->paginate(20);

        return view('migrationtools::admin.migration.index', compact('runs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'source_type' => ['required', 'in:wxr_xml,wp_rest,csv'],
            'base_url' => ['nullable', 'url'],
            'dry_run' => ['nullable', 'boolean'],
            'download_media' => ['nullable', 'boolean'],
            'create_redirects' => ['nullable', 'boolean'],
            'locale' => ['nullable', 'string', 'max:8'],
            'overwrite_strategy' => ['nullable', 'in:skip_if_exists,update_if_exists'],
            'wxr_file' => ['nullable', 'file'],
            'csv_file' => ['nullable', 'file'],
        ]);

        $options = [
            'source_type' => $data['source_type'],
            'base_url' => $data['base_url'] ?? null,
            'dry_run' => (bool) ($data['dry_run'] ?? false),
            'download_media' => (bool) ($data['download_media'] ?? false),
            'create_redirects' => (bool) ($data['create_redirects'] ?? false),
            'locale' => $data['locale'] ?? config('locales.default', 'en'),
            'overwrite_strategy' => $data['overwrite_strategy'] ?? 'skip_if_exists',
        ];

        if ($request->hasFile('wxr_file')) {
            $options['wxr_content'] = file_get_contents($request->file('wxr_file')->getRealPath()) ?: '';
        }
        if ($request->hasFile('csv_file')) {
            $options['csv_content'] = file_get_contents($request->file('csv_file')->getRealPath()) ?: '';
        }

        $run = MigrationRun::query()->create([
            'source_type' => $data['source_type'],
            'status' => 'queued',
            'options' => $options,
        ]);

        dispatch(new ProcessMigrationRunJob($run->id));

        return redirect()->route('admin.migration.show', $run)->with('status', 'Import queued.');
    }

    public function show(MigrationRun $migration)
    {
        $migration->load(['items' => fn ($q) => $q->latest()->limit(200)]);

        return view('migrationtools::admin.migration.show', ['run' => $migration]);
    }

    public function retryFailed(MigrationRun $migration)
    {
        $migration->items()->where('status', 'failed')->update(['status' => 'pending']);
        dispatch(new ProcessMigrationRunJob($migration->id));

        return back()->with('status', 'Retry queued.');
    }

    public function exports()
    {
        return view('migrationtools::admin.migration.exports');
    }
}
