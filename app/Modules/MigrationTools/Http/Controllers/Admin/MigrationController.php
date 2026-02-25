<?php

namespace App\Modules\MigrationTools\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\MigrationTools\Jobs\ProcessMigrationRunJob;
use App\Modules\MigrationTools\Models\MigrationItem;
use App\Modules\MigrationTools\Models\MigrationProfile;
use App\Modules\MigrationTools\Models\MigrationRun;
use App\Modules\MigrationTools\Services\CsvImportService;
use App\Modules\MigrationTools\Services\MigrationManager;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function profiles()
    {
        $profiles = MigrationProfile::query()->latest()->paginate(20);

        return view('migrationtools::admin.migration.profiles', compact('profiles'));
    }

    public function storeProfile(Request $request, CsvImportService $csvImportService)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'source_type' => ['required', 'in:wxr_xml,wp_rest,csv'],
            'base_url' => ['nullable', 'url'],
            'properties_endpoint' => ['nullable', 'string', 'max:255'],
            'delta_strategy' => ['nullable', 'in:full,since_last_run,since_datetime'],
            'overwrite_strategy' => ['nullable', 'in:skip_if_exists,update_if_exists'],
            'field_mapping' => ['nullable', 'array'],
            'field_mapping.title' => ['nullable', 'string', 'max:120'],
            'field_mapping.slug' => ['nullable', 'string', 'max:120'],
            'field_mapping.base_price_per_night' => ['nullable', 'string', 'max:120'],
            'field_mapping.max_guests' => ['nullable', 'string', 'max:120'],
        ]);

        if (($data['source_type'] ?? null) === 'csv') {
            $missing = $csvImportService->validateMapping($data['field_mapping'] ?? []);
            if ($missing !== []) {
                return back()->withErrors(['field_mapping' => 'Missing required mapping: '.implode(', ', $missing)])->withInput();
            }
        }

        MigrationProfile::query()->create([
            'name' => $data['name'],
            'source_type' => $data['source_type'],
            'base_url' => $data['base_url'] ?? null,
            'endpoints' => ['properties' => $data['properties_endpoint'] ?? '/wp-json/wp/v2/properties'],
            'field_mapping' => $data['field_mapping'] ?? [],
            'overwrite_strategy' => $data['overwrite_strategy'] ?? 'skip_if_exists',
            'delta_strategy' => $data['delta_strategy'] ?? 'full',
        ]);

        return redirect()->route('admin.migration.profiles')->with('status', 'Profile created.');
    }

    public function runs()
    {
        $runs = MigrationRun::query()->with('profile')->latest()->paginate(20);
        $profiles = MigrationProfile::query()->orderBy('name')->get();

        return view('migrationtools::admin.migration.runs', compact('runs', 'profiles'));
    }

    public function runFromProfile(Request $request)
    {
        $data = $request->validate([
            'profile_id' => ['required', 'exists:migration_profiles,id'],
            'dry_run' => ['nullable', 'boolean'],
            'since_datetime' => ['nullable', 'date'],
        ]);

        $profile = MigrationProfile::query()->findOrFail($data['profile_id']);
        $options = [
            'source_type' => $profile->source_type,
            'base_url' => $profile->base_url,
            'properties_endpoint' => $profile->endpoints['properties'] ?? '/wp-json/wp/v2/properties',
            'field_mapping' => $profile->field_mapping ?? [],
            'overwrite_strategy' => $profile->overwrite_strategy,
            'delta_strategy' => $profile->delta_strategy,
            'dry_run' => (bool) ($data['dry_run'] ?? false),
        ];
        if ($profile->delta_strategy === 'since_datetime' && ! empty($data['since_datetime'])) {
            $options['modified_since'] = $data['since_datetime'];
        }

        $run = MigrationRun::query()->create([
            'profile_id' => $profile->id,
            'source_type' => $profile->source_type,
            'status' => 'queued',
            'options' => $options,
        ]);

        dispatch(new ProcessMigrationRunJob($run->id));

        return redirect()->route('admin.migration.runs.show', $run)->with('status', 'Run queued.');
    }

    public function showRun(MigrationRun $run)
    {
        $run->load(['profile', 'items' => fn ($q) => $q->latest()->limit(250)]);

        return view('migrationtools::admin.migration.run-show', compact('run'));
    }

    public function retryFailed(MigrationRun $run)
    {
        $run->items()->where('status', 'failed')->update(['status' => 'pending', 'result' => null]);
        dispatch(new ProcessMigrationRunJob($run->id));

        return back()->with('status', 'Retry queued.');
    }

    public function rollbackRun(MigrationRun $run, MigrationManager $migrationManager)
    {
        $result = $migrationManager->rollbackRun($run);

        return back()->with('status', "Rollback done. Deleted {$result['deleted']} items, skipped {$result['skipped']} items.");
    }

    public function rollbackItem(MigrationItem $item, MigrationManager $migrationManager)
    {
        $ok = $migrationManager->rollbackItem($item);

        return back()->with('status', $ok ? 'Item rolled back.' : 'Item rollback skipped (not safe).');
    }

    public function csvMappingWizard(Request $request, CsvImportService $csvImportService)
    {
        $data = $request->validate([
            'csv_file' => ['required', 'file'],
            'mapping' => ['nullable', 'array'],
        ]);

        $content = file_get_contents($request->file('csv_file')->getRealPath()) ?: '';
        $parsed = $csvImportService->parse($content, $data['mapping'] ?? []);
        $missing = $csvImportService->validateMapping($data['mapping'] ?? []);

        if ($missing !== []) {
            return back()->withErrors(['mapping' => 'Missing required mapping: '.implode(', ', $missing)]);
        }

        return back()->with('status', 'Mapping valid. Preview lines: '.count($parsed['preview']));
    }

    public function exports()
    {
        return view('migrationtools::admin.migration.exports');
    }
}
