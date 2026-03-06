<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PostResource;
use App\Modules\Blog\Models\Post;

class BlogPublicApiController extends ApiController
{
    public function index()
    {
        $this->resolveLocale();

        $posts = Post::query()
            ->published()
            ->with(['translations', 'categories.translations'])
            ->when(request('category'), fn ($q, $v) => $q->whereHas('categories', fn ($sq) => $sq->where('slug', $v)))
            ->latest('published_at')
            ->paginate((int) request('per_page', 15))
            ->withQueryString();

        return PostResource::collection($posts);
    }

    public function show(string $slug)
    {
        $this->resolveLocale();

        $post = Post::query()
            ->published()
            ->with(['translations', 'categories.translations'])
            ->where('slug', $slug)
            ->firstOrFail();

        return PostResource::make($post);
    }
}
