<?php

namespace App\Modules\Forms\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Forms\FormSubmission;
use App\Modules\Forms\Http\Requests\Public\ContactSubmissionRequest;
use App\Modules\Forms\Http\Requests\Public\QuoteSubmissionRequest;
use App\Core\Settings\SettingsService;
use Illuminate\Support\Facades\Mail;

class FormSubmissionController extends Controller
{
    public function __construct(private readonly SettingsService $settingsService)
    {
    }

    public function storeContact(ContactSubmissionRequest $request)
    {
        if ($this->settingsService->get('forms', 'captcha_enabled', false) && ! $request->filled('captcha_token')) {
            return back()->withErrors(['captcha' => 'Captcha required.'])->withInput();
        }

        $submission = FormSubmission::query()->create([
            'form_type' => 'contact',
            'locale' => app()->getLocale(),
            'payload' => $request->validated(),
            'status' => 'new',
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => (string) $request->userAgent(),
            'source_url' => (string) url()->previous(),
        ]);

        $this->notify($submission);

        return back()->with('status', 'Votre message a été envoyé.');
    }

    public function storeQuote(QuoteSubmissionRequest $request)
    {
        if ($this->settingsService->get('forms', 'captcha_enabled', false) && ! $request->filled('captcha_token')) {
            return back()->withErrors(['captcha' => 'Captcha required.'])->withInput();
        }

        $submission = FormSubmission::query()->create([
            'form_type' => 'quote',
            'locale' => app()->getLocale(),
            'payload' => $request->validated(),
            'status' => 'new',
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => (string) $request->userAgent(),
            'source_url' => (string) url()->previous(),
        ]);

        $this->notify($submission);

        return back()->with('status', 'Votre demande de devis a été envoyée.');
    }

    private function notify(FormSubmission $submission): void
    {
        $to = config('mail.from.address');
        if (! $to) {
            return;
        }

        Mail::raw('New '.$submission->form_type.' submission #'.$submission->id, fn ($msg) => $msg->to($to)->subject('New '.$submission->form_type.' submission'));
    }
}
