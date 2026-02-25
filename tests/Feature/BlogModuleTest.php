<?php

namespace Tests\Feature;

use App\Modules\Blog\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BlogModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_listing_returns_200(): void
    {
        $this->seed();

        $this->get('/blog')->assertOk();
    }

    public function test_blog_show_published_200_and_draft_404(): void
    {
        $this->seed();

        $published = Post::query()->where('status', 'published')->firstOrFail();
        $draft = Post::query()->create(['slug' => 'draft-post', 'status' => 'draft']);
        $draft->translations()->create(['locale' => 'en', 'title' => 'Draft post']);

        $this->get('/blog/'.$published->slug)->assertOk();
        $this->get('/blog/'.$draft->slug)->assertNotFound();
    }

    public function test_fr_blog_returns_200(): void
    {
        $this->seed();

        $this->get('/fr/blog')->assertOk();
    }

    public function test_blog_category_archive_filters_correctly(): void
    {
        $this->seed();

        $this->get('/blog/category/news')->assertOk()->assertSee('RestInnov launches quality framework');
    }

    public function test_install_lock_and_realestate_routes_non_regression(): void
    {
        $this->seed();

        File::put(storage_path('app/install.lock'), 'locked');
        $this->get('/install')->assertRedirect(route('admin.login'));
        File::delete(storage_path('app/install.lock'));

        $this->get('/our-properties')->assertOk();
    }
}
