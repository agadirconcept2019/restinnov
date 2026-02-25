<?php

namespace App\Modules\Blog\Http\Controllers\Admin;

use App\Core\Audit\AuditLogger;
use App\Http\Controllers\Controller;
use App\Modules\Blog\Http\Requests\Admin\StorePostRequest;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\PostCategory;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::query()
            ->with(['translations', 'categories.translations'])
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->when(request('category'), fn ($q, $v) => $q->whereHas('categories', fn ($sq) => $sq->where('slug', $v)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = PostCategory::query()->with('translations')->get();

        return view('blog::admin.posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        return view('blog::admin.posts.form', ['post' => new Post, 'categories' => PostCategory::with('translations')->get()]);
    }

    public function store(StorePostRequest $request, AuditLogger $auditLogger)
    {
        $post = Post::query()->create([
            'slug' => $request->input('slug'),
            'status' => $request->input('status'),
            'published_at' => $request->input('status') === 'published' ? ($request->input('published_at') ?: now()) : null,
            'author_id' => auth()->id(),
            'is_featured' => (bool) $request->boolean('is_featured', false),
        ]);

        $this->syncPost($post, $request->validated());
        $auditLogger->log($post->status === 'published' ? 'post.published' : 'post.created', $post, ['slug' => $post->slug]);

        return redirect()->route('admin.blog.posts.edit', $post)->with('status', 'Post created.');
    }

    public function edit(Post $post)
    {
        $post->load(['translations', 'categories']);

        return view('blog::admin.posts.form', ['post' => $post, 'categories' => PostCategory::with('translations')->get()]);
    }

    public function update(StorePostRequest $request, Post $post, AuditLogger $auditLogger)
    {
        $post->update([
            'slug' => $request->input('slug'),
            'status' => $request->input('status'),
            'published_at' => $request->input('status') === 'published' ? ($request->input('published_at') ?: now()) : null,
            'is_featured' => (bool) $request->boolean('is_featured', false),
        ]);

        $this->syncPost($post, $request->validated());
        $auditLogger->log($post->status === 'published' ? 'post.published' : 'post.updated', $post, ['slug' => $post->slug]);

        return back()->with('status', 'Post updated.');
    }

    public function destroy(Post $post, AuditLogger $auditLogger)
    {
        $auditLogger->log('post.deleted', $post, ['slug' => $post->slug]);
        $post->delete();

        return redirect()->route('admin.blog.posts.index')->with('status', 'Post deleted.');
    }

    private function syncPost(Post $post, array $data): void
    {
        foreach (['en', 'fr', 'es'] as $locale) {
            if (empty($data['title_'.$locale])) {
                continue;
            }

            $post->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $data['title_'.$locale],
                    'excerpt' => $data['excerpt_'.$locale] ?? null,
                    'content' => $data['content_'.$locale] ?? null,
                    'meta_title' => $data['meta_title_'.$locale] ?? null,
                    'meta_description' => $data['meta_description_'.$locale] ?? null,
                    'canonical_url' => $data['canonical_url_'.$locale] ?? null,
                ],
            );
        }

        $post->categories()->sync($data['category_ids'] ?? []);
    }
}
