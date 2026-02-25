<?php

namespace App\Core\Install;

use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;

class InstallLogger
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/install.log'),
            'replace_placeholders' => true,
        ]);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
}
