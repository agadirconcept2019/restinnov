<?php

namespace App\Modules\Communications\Services;

use App\Modules\Communications\Models\EmailLog;

class EmailLogService
{
    public function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, 'unknown.local');
        $prefix = mb_substr($local, 0, 1);

        return $prefix.'***@'.$domain;
    }

    public function createQueued(array $payload): EmailLog
    {
        return EmailLog::query()->create($payload + ['status' => 'queued', 'queued_at' => now(), 'attempts' => 0]);
    }
}
