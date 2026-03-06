<?php

namespace App\Core\Install;

use Illuminate\Support\Facades\File;

class EnvWriter
{
    public function ensureEnvExists(): void
    {
        if (! File::exists(base_path('.env'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
        }
    }

    public function write(array $pairs): void
    {
        $this->ensureEnvExists();

        $path = base_path('.env');
        $content = (string) File::get($path);

        foreach ($pairs as $key => $value) {
            $line = sprintf('%s=%s', $key, $this->formatValue((string) $value));
            $pattern = "/^{$key}=.*$/m";

            $content = preg_match($pattern, $content)
                ? (string) preg_replace($pattern, $line, $content)
                : trim($content).PHP_EOL.$line.PHP_EOL;
        }

        File::put($path, $content);
    }

    private function formatValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        return preg_match('/\s/', $value) ? '"'.str_replace('"', '\\"', $value).'"' : $value;
    }
}
