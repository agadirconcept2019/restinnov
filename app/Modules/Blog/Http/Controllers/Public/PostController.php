<?php

namespace App\Modules\Blog\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\PostCategory;
use App\Modules\Blog\Services\PostQueryService;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    public function index(PostQueryService $service)
    {
        $posts = $service->listPublished();
        $categories = $this->categories();

        return view('blog::public.index', compact('posts', 'categories'));
    }

    public function show(string $slug)
    {
        $post = Post::query()->with(['translations', 'categories.translations'])->published()->where('slug', $slug)->firstOrFail();

        return view('blog::public.show', compact('post'));
    }

    public function byCategory(string $slug, PostQueryService $service)
    {
        $posts = $service->listPublished(['category' => $slug]);
        $categories = $this->categories();

        return view('blog::public.index', compact('posts', 'categories'));
    }

    private function categories()
    {
        return Cache::remember('blog:categories:active', now()->addMinutes(15), fn () => PostCategory::with('translations')->where('is_active', true)->orderBy('sort_order')->get());
    }
}
