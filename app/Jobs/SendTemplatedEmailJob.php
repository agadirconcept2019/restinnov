<?php

namespace App\Jobs;

use App\Core\Mail\TemplateRenderer;
use App\Modules\Communications\Models\EmailLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendTemplatedEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $to,
        private readonly string $templateKey,
        private readonly array $variables = [],
        private readonly ?string $locale = null,
        private readonly ?int $emailLogId = null,
    ) {
    }

    public function handle(TemplateRenderer $renderer): void
    {
        $log = $this->emailLogId ? EmailLog::query()->find($this->emailLogId) : null;
        if ($log) {
            $log->increment('attempts');
        }

        try {
            $rendered = $renderer->render($this->templateKey, $this->variables, $this->locale);

            Mail::html($rendered['body_html'], function ($message) use ($rendered): void {
                $message->to($this->to)
                    ->subject($rendered['subject']);
            });

            if ($log) {
                $log->update(['status' => 'sent', 'sent_at' => now(), 'subject' => $rendered['subject'], 'last_error_excerpt' => null]);
            }
        } catch (\Throwable $exception) {
            if ($log) {
                $log->update(['status' => 'failed', 'failed_at' => now(), 'last_error_excerpt' => (string) str($exception->getMessage())->limit(300)]);
            }
        }
    }
}
