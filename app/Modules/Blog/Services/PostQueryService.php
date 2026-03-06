<?php

namespace App\Modules\Blog\Services;

use App\Modules\Blog\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostQueryService
{
    public function listPublished(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Post::query()
            ->with(['translations', 'categories.translations'])
            ->published()
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('categories', fn ($sq) => $sq->where('slug', $slug)))
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
