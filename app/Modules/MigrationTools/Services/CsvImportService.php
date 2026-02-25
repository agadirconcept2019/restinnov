<?php

namespace App\Modules\MigrationTools\Services;

class CsvImportService
{
    public function parse(string $csv): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $csv)));
        if (count($lines) < 2) {
            return ['properties' => []];
        }

        $headers = str_getcsv(array_shift($lines));
        $properties = [];
        foreach ($lines as $line) {
            $row = array_combine($headers, str_getcsv($line));
            $properties[] = [
                'source_id' => (string) ($row['id'] ?? ''),
                'slug' => $row['slug'] ?? str($row['title'] ?? 'property')->slug()->toString(),
                'title' => $row['title'] ?? '',
                'status' => $row['status'] ?? 'published',
                'base_price_per_night' => (float) ($row['base_price_per_night'] ?? 0),
                'max_guests' => (int) ($row['max_guests'] ?? 1),
            ];
        }

        return ['properties' => $properties];
    }
}
