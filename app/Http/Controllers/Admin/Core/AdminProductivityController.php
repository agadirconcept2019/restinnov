<?php

namespace App\Http\Controllers\Admin\Core;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Jobs\RunAdminExportJob;
use App\Models\Core\ExportRun;
use App\Models\Core\UserSavedView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProductivityController extends Controller
{
    public function saveView(Request $request)
    {
        $data = $request->validate([
            'resource_key' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:120'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $query = collect($request->query())->except(['page'])->all();
        if ($request->boolean('is_default')) {
            UserSavedView::query()->where('user_id', auth()->id())->where('resource_key', $data['resource_key'])->update(['is_default' => false]);
        }

        UserSavedView::query()->updateOrCreate(
            ['user_id' => auth()->id(), 'resource_key' => $data['resource_key'], 'name' => $data['name']],
            ['query_json' => $query, 'is_default' => $request->boolean('is_default')],
        );

        return back()->with('status', 'Saved view stored.');
    }

    public function applyView(UserSavedView $view)
    {
        abort_unless($view->user_id === auth()->id(), 403);

        $target = url()->previous();
        $path = parse_url($target, PHP_URL_PATH) ?: '/admin';

        return redirect()->to(url($path).'?'.http_build_query($view->query_json));
    }

    public function deleteView(UserSavedView $view)
    {
        abort_unless($view->user_id === auth()->id(), 403);
        $view->delete();

        return back()->with('status', 'Saved view deleted.');
    }

    public function queueExport(Request $request, AuditLogger $auditLogger)
    {
        $data = $request->validate(['resource_key' => ['required', 'string', 'max:80']]);

        $run = ExportRun::query()->create([
            'user_id' => auth()->id(),
            'resource_key' => $data['resource_key'],
            'status' => 'queued',
            'disk' => 'local',
        ]);

        RunAdminExportJob::dispatch($run->id);
        $auditLogger->log('admin.export.queued', null, ['resource' => $run->resource_key, 'export_run_id' => $run->id]);

        return back()->with('status', 'Export queued.');
    }

    public function exports()
    {
        $runs = ExportRun::query()->where('user_id', auth()->id())->latest()->paginate(20);

        return view('admin.exports.index', compact('runs'));
    }

    public function downloadExport(ExportRun $run)
    {
        abort_unless($run->user_id === auth()->id(), 403);
        abort_unless($run->status === 'completed' && $run->file_path, 404);

        return Storage::disk($run->disk)->download($run->file_path, basename($run->file_path));
    }
}
