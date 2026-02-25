<?php

namespace App\Modules\MigrationTools\Services;

use App\Modules\MigrationTools\DTO\PropertyDTO;

class CsvImportService
{
    public function parse(string $csv, array $mapping = []): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $csv)));
        if (count($lines) < 2) {
            return ['properties' => [], 'preview' => []];
        }

        $headers = str_getcsv(array_shift($lines));
        $preview = array_slice($lines, 0, 5);

        $resolve = function (array $row, string $field) use ($mapping) {
            $column = $mapping[$field] ?? $field;

            return $row[$column] ?? null;
        };

        $properties = [];
        foreach ($lines as $line) {
            $row = array_combine($headers, str_getcsv($line));
            $properties[] = (new PropertyDTO(
                sourceId: (string) ($resolve($row, 'id') ?? ''),
                slug: (string) ($resolve($row, 'slug') ?? str($resolve($row, 'title') ?? 'property')->slug()->toString()),
                title: (string) ($resolve($row, 'title') ?? ''),
                status: (string) ($resolve($row, 'status') ?? 'published'),
                basePricePerNight: (float) ($resolve($row, 'base_price_per_night') ?? 0),
                maxGuests: (int) ($resolve($row, 'max_guests') ?? 1),
                description: (string) ($resolve($row, 'description') ?? ''),
                modifiedGmt: $resolve($row, 'modified_gmt'),
            ))->toArray();
        }

        return ['properties' => $properties, 'preview' => $preview];
    }

    public function validateMapping(array $mapping): array
    {
        $required = ['title', 'slug', 'base_price_per_night', 'max_guests'];
        $missing = array_values(array_filter($required, fn ($f) => empty($mapping[$f])));

        return $missing;
    }
}
