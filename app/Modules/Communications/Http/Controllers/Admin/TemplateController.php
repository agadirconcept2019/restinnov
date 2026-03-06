<?php

namespace App\Modules\Communications\Http\Controllers\Admin;

use App\Core\Mail\TemplateRenderer;
use App\Http\Controllers\Controller;
use App\Models\Core\EmailTemplate;
use App\Modules\Communications\Services\EmailDispatcher;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::query()->orderBy('key')->orderBy('locale')->paginate(30);

        return view('communications::admin.communications.templates', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:120'],
            'locale' => ['required', 'string', 'max:8'],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        EmailTemplate::query()->updateOrCreate(
            ['key' => $data['key'], 'locale' => $data['locale']],
            [
                'subject' => $data['subject'],
                'body_html' => $data['body_html'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'updated_by' => auth()->id(),
            ],
        );

        return back()->with('status', 'Template saved.');
    }

    public function preview(Request $request, TemplateRenderer $renderer)
    {
        $data = $request->validate(['key' => ['required', 'string'], 'locale' => ['nullable', 'string', 'max:8']]);

        return response()->json($renderer->preview($data['key'], $data['locale'] ?? null));
    }

    public function sendTest(Request $request, EmailDispatcher $dispatcher)
    {
        $data = $request->validate([
            'to' => ['required', 'email'],
            'key' => ['required', 'string', 'max:120'],
            'locale' => ['nullable', 'string', 'max:8'],
        ]);

        $dispatcher->queue($data['to'], $data['key'], [], $data['locale'] ?? null, 'template-test', null);

        return back()->with('status', 'Test email queued.');
    }
}
