<?php

namespace App\Jobs;

use App\Models\Forms\FormSubmission;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendFormNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $submissionId)
    {
    }

    public function handle(): void
    {
        $submission = FormSubmission::query()->find($this->submissionId);
        if (! $submission) {
            return;
        }

        $to = config('mail.from.address');
        if (! $to) {
            return;
        }

        try {
            Mail::raw('New '.$submission->form_type.' submission #'.$submission->id, fn ($msg) => $msg->to($to)->subject('New '.$submission->form_type.' submission'));
        } catch (\Throwable $e) {
            Log::warning('forms.notification_failed', [
                'submission_id' => $submission->id,
                'form_type' => $submission->form_type,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
