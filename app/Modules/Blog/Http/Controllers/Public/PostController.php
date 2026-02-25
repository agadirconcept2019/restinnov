<?php

namespace App\Modules\Blog\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Services\PostQueryService;

class PostController extends Controller
{
    public function index(PostQueryService $service)
    {
        $posts = $service->listPublished();

        return view('blog::public.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::query()->with(['translations', 'categories.translations'])->published()->where('slug', $slug)->firstOrFail();

        return view('blog::public.show', compact('post'));
    }

    public function byCategory(string $slug, PostQueryService $service)
    {
        $posts = $service->listPublished(['category' => $slug]);

        return view('blog::public.index', compact('posts'));
    }
}
