<?php

namespace App\Modules\MigrationTools\Services;

use Illuminate\Support\Facades\Http;

class RemoteMediaDownloader
{
    public function isSafeUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'] ?? null;
        if (! $host) {
            return false;
        }

        $ip = gethostbyname($host);
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    public function download(string $url): ?string
    {
        if (! $this->isSafeUrl($url)) {
            return null;
        }

        $response = Http::timeout(8)->withOptions(['allow_redirects' => ['max' => 2]])->get($url);
        if (! $response->successful()) {
            return null;
        }

        if (strlen($response->body()) > 5 * 1024 * 1024) {
            return null;
        }

        $name = 'migration/'.sha1($response->body()).'.bin';
        \Storage::disk('public')->put($name, $response->body());

        return $name;
    }
}
