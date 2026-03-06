<?php

namespace App\Modules\OwnerPortal\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOwnerPortalMailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $to,
        private readonly string $subject,
        private readonly string $body,
    ) {}

    public function handle(): void
    {
        try {
            Mail::raw($this->body, fn ($m) => $m->to($this->to)->subject($this->subject));
        } catch (\Throwable $e) {
            Log::warning('owner_portal_mail_failed', ['to_hash' => hash('sha256', $this->to), 'error' => $e->getMessage()]);
        }
    }
}
