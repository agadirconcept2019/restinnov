<?php

namespace App\Jobs;

use App\Core\Mail\TemplateRenderer;
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
    ) {
    }

    public function handle(TemplateRenderer $renderer): void
    {
        $rendered = $renderer->render($this->templateKey, $this->variables, $this->locale);

        Mail::html($rendered['body_html'], function ($message) use ($rendered): void {
            $message->to($this->to)
                ->subject($rendered['subject']);
        });
    }
}
