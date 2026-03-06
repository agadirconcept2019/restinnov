<?php

namespace App\Modules\RealEstate\Services;

use App\Models\RealEstate\PropertyAvailability;
use App\Models\RealEstate\PropertyIcalFeed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IcalService
{
    public function validateUrl(string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        if (! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'] ?? '';
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }

        return true;
    }

    public function sync(PropertyIcalFeed $feed): int
    {
        if (! $this->validateUrl($feed->feed_url)) {
            throw new \RuntimeException('Invalid iCal URL.');
        }

        $content = Http::timeout(8)->accept('text/calendar')->get($feed->feed_url)->throw()->body();
        if (strlen($content) > 1024 * 1024) {
            throw new \RuntimeException('iCal too large.');
        }

        $events = $this->parseEvents($content);
        $count = 0;

        foreach ($events as [$from, $to]) {
            $start = new \DateTimeImmutable($from);
            $end = new \DateTimeImmutable($to);
            for ($d = $start; $d < $end; $d = $d->modify('+1 day')) {
                PropertyAvailability::query()->updateOrCreate(
                    ['property_id' => $feed->property_id, 'date' => $d->format('Y-m-d')],
                    ['status' => 'booked'],
                );
                $count++;
            }
        }

        return $count;
    }

    /** @return array<int,array{0:string,1:string}> */
    public function parseEvents(string $ics): array
    {
        $events = [];
        preg_match_all('/BEGIN:VEVENT(.*?)END:VEVENT/s', $ics, $matches);
        foreach ($matches[1] ?? [] as $block) {
            preg_match('/DTSTART[^:]*:(\d{8})/', $block, $start);
            preg_match('/DTEND[^:]*:(\d{8})/', $block, $end);
            if (! empty($start[1]) && ! empty($end[1])) {
                $events[] = [
                    Str::of($start[1])->substr(0, 4).'-'.Str::of($start[1])->substr(4, 2).'-'.Str::of($start[1])->substr(6, 2),
                    Str::of($end[1])->substr(0, 4).'-'.Str::of($end[1])->substr(4, 2).'-'.Str::of($end[1])->substr(6, 2),
                ];
            }
        }

        return $events;
    }

    public function exportIcs(int $propertyId): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//RestInnov//RealEstate//EN',
        ];

        $days = PropertyAvailability::query()->where('property_id', $propertyId)->whereIn('status', ['booked', 'blocked'])->orderBy('date')->get();
        foreach ($days as $day) {
            $date = str_replace('-', '', $day->date);
            $next = date('Ymd', strtotime($day->date.' +1 day'));
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'DTSTART;VALUE=DATE:'.$date;
            $lines[] = 'DTEND;VALUE=DATE:'.$next;
            $lines[] = 'SUMMARY:Unavailable';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }
}
