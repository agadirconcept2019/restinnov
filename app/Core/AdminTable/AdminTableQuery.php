<?php

namespace App\Core\AdminTable;

use Illuminate\Database\Eloquent\Builder;

class AdminTableQuery
{
    public function apply(Builder $query, array $config): Builder
    {
        $search = trim((string) request('q', ''));
        if ($search !== '' && ! empty($config['search'])) {
            $query->where(function (Builder $q) use ($config, $search) {
                foreach ($config['search'] as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        foreach ($config['filters'] ?? [] as $key => $callback) {
            $value = request($key);
            if ($value === null || $value === '') {
                continue;
            }

            $callback($query, $value);
        }

        $sort = request('sort', $config['default_sort'] ?? 'id');
        $dir = request('dir', $config['default_dir'] ?? 'desc');
        $allowed = $config['sorts'] ?? [];

        if (! in_array($sort, $allowed, true)) {
            $sort = $config['default_sort'] ?? 'id';
        }

        if (! in_array($dir, ['asc', 'desc'], true)) {
            $dir = $config['default_dir'] ?? 'desc';
        }

        return $query->orderBy($sort, $dir);
    }
}
