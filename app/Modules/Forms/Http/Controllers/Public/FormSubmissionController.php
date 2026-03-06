<?php

namespace App\Modules\Forms\Http\Controllers\Public;

use App\Core\Settings\SettingsService;
use App\Http\Controllers\Controller;
use App\Jobs\SendFormNotificationJob;
use App\Models\Forms\FormSubmission;
use App\Modules\Forms\Http\Requests\Public\ContactSubmissionRequest;
use App\Modules\Forms\Http\Requests\Public\QuoteSubmissionRequest;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

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

        $submission = FormSubmission::query()->create($this->payload('contact', $request->validated(), $request));
        $this->notify($submission);

        return back()->with('status', 'Votre message a été envoyé.');
    }

    public function storeQuote(QuoteSubmissionRequest $request)
    {
        if ($this->settingsService->get('forms', 'captcha_enabled', false) && ! $request->filled('captcha_token')) {
            return back()->withErrors(['captcha' => 'Captcha required.'])->withInput();
        }

        $submission = FormSubmission::query()->create($this->payload('quote', $request->validated(), $request));
        $this->notify($submission);

        return back()->with('status', 'Votre demande de devis a été envoyée.');
    }

    private function payload(string $formType, array $validated, $request): array
    {
        unset($validated['company_name'], $validated['submitted_at'], $validated['captcha_token']);

        return [
            'form_type' => $formType,
            'locale' => app()->getLocale(),
            'payload' => $validated,
            'status' => 'new',
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => (string) $request->userAgent(),
            'source_url' => (string) url()->previous(),
            'submitted_at' => (int) $request->input('submitted_at'),
        ];
    }

    private function notify(FormSubmission $submission): void
    {
        try {
            Bus::dispatch(new SendFormNotificationJob($submission->id));
        } catch (\Throwable $e) {
            Log::warning('forms.notification_dispatch_failed', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
