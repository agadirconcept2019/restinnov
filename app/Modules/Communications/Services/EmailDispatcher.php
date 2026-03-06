<?php

namespace App\Modules\Communications\Services;

use App\Jobs\SendTemplatedEmailJob;
use App\Modules\Communications\Models\EmailLog;
use Illuminate\Support\Facades\Bus;

class EmailDispatcher
{
    public function __construct(private readonly EmailLogService $emailLogService)
    {
    }

    public function queue(string $to, string $templateKey, array $variables = [], ?string $locale = null, ?string $relatedType = null, ?int $relatedId = null): EmailLog
    {
        $log = $this->emailLogService->createQueued([
            'template_key' => $templateKey,
            'locale' => $locale,
            'to_email_masked' => $this->emailLogService->maskEmail($to),
            'to_email_encrypted' => encrypt($to),
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'meta' => [
                'keys' => array_keys($variables),
                'related' => $relatedType ? $relatedType.':'.$relatedId : null,
                'variables' => $variables,
            ],
        ]);

        rescue(fn () => Bus::dispatch(new SendTemplatedEmailJob($to, $templateKey, $variables, $locale, $log->id)), report: false);

        return $log;
    }

    public function retry(EmailLog $log): bool
    {
        if ($log->attempts >= 3 || ! $log->to_email_encrypted) {
            return false;
        }

        $to = decrypt($log->to_email_encrypted);
        $payload = $log->payload_encrypted ? json_decode((string) decrypt($log->payload_encrypted), true) : [];
        $variables = (array) ($payload['variables'] ?? []);

        $log->update(['status' => 'queued', 'queued_at' => now(), 'failed_at' => null, 'last_error_excerpt' => null]);
        Bus::dispatch(new SendTemplatedEmailJob($to, $log->template_key ?? 'generic', $variables, $log->locale, $log->id));

        return true;
    }
}
