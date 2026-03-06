<?php

namespace App\Core\AdminTable;

use App\Models\Core\UserSavedView;

class SavedViewService
{
    public function currentFor(string $resourceKey): array
    {
        $query = request()->query();
        if (! empty($query)) {
            return $query;
        }

        $default = UserSavedView::query()
            ->where('user_id', auth()->id())
            ->where('resource_key', $resourceKey)
            ->where('is_default', true)
            ->first();

        return $default?->query_json ?? [];
    }
}
