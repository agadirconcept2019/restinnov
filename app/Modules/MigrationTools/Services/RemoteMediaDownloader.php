<?php

namespace App\Modules\MigrationTools\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RemoteMediaDownloader
{
    public function isSafeUrl(string $url, array $allowedHosts = []): bool
    {
        $parts = parse_url($url);
        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'] ?? null;
        if (! $host) {
            return false;
        }

        if ($allowedHosts !== [] && ! in_array($host, $allowedHosts, true)) {
            return false;
        }

        $records = dns_get_record($host, DNS_A + DNS_AAAA) ?: [];
        if ($records === []) {
            $records[] = ['ip' => gethostbyname($host)];
        }

        foreach ($records as $record) {
            $ip = $record['ip'] ?? ($record['ipv6'] ?? null);
            if (! $ip) {
                continue;
            }
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    public function download(string $url, array $allowedHosts = []): ?string
    {
        if (! $this->isSafeUrl($url, $allowedHosts)) {
            return null;
        }

        $response = Http::timeout(10)->withOptions(['allow_redirects' => ['max' => 2, 'track_redirects' => true]])->get($url);
        if (! $response->successful()) {
            return null;
        }

        $effective = $response->effectiveUri();
        if ($effective && ! $this->isSafeUrl((string) $effective, $allowedHosts)) {
            return null;
        }

        if (strlen($response->body()) > 5 * 1024 * 1024) {
            return null;
        }

        $hash = hash('sha256', $response->body());
        $name = 'migration/'.$hash.'-'.strlen($response->body()).'.bin';

        if (! Storage::disk('public')->exists($name)) {
            Storage::disk('public')->put($name, $response->body());
        }

        return $name;
    }
}
