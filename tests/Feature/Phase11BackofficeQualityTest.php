<?php

namespace Tests\Feature;

use App\Models\CmsPages\Page;
use App\Models\Core\Menu;
use App\Models\Core\MenuItem;
use App\Models\Core\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase11BackofficeQualityTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_blocks_sensitive_endpoints_by_role(): void
    {
        $editor = $this->userForRole('editor');
        $this->actingAs($editor)->post('/admin/ops/jobs/retry')->assertForbidden();

        $support = $this->userForRole('support');
        $this->actingAs($support)->get('/admin/migration/profiles')->assertForbidden();

        $contentManager = $this->userForRole('content_manager');
        $this->actingAs($contentManager)->get('/admin/redirects')->assertForbidden();

        $owner = $this->userForRole('owner');
        $this->actingAs($owner)->get('/admin/communications/logs')->assertForbidden();
    }

    public function test_cms_content_is_sanitized_and_seo_meta_saved(): void
    {
        $user = $this->userForRole('content_manager');
        $this->actingAs($user)->post('/admin/cms-pages/pages', [
            'slug' => 'safe-page',
            'template' => 'services',
            'status' => 'draft',
            'title_en' => 'Safe page',
            'content_en' => '<p>Hello</p><script>alert(1)</script><img src="x" onerror="alert(1)"><a href="javascript:alert(1)">bad</a>',
            'meta_title_en' => 'SEO title',
            'meta_description_en' => 'SEO description',
            'canonical_url_en' => 'https://example.test/safe-page',
            'robots_en' => 'noindex,nofollow',
            'schema_json_en' => '{"@type":"Article"}',
        ])->assertRedirect();

        $page = Page::query()->where('slug', 'safe-page')->firstOrFail();
        $content = (string) $page->translations()->where('locale', 'en')->value('content');

        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onerror', $content);
        $this->assertStringNotContainsString('javascript:', $content);

        $this->assertDatabaseHas('seo_meta', [
            'metaable_type' => Page::class,
            'metaable_id' => $page->id,
            'locale' => 'en',
            'meta_title' => 'SEO title',
            'robots' => 'noindex,nofollow',
        ]);
    }

    public function test_media_upload_stores_variants_meta(): void
    {
        Storage::fake('public');
        $user = $this->userForRole('content_manager');

        $this->actingAs($user)->post('/admin/media', [
            'file' => UploadedFile::fake()->image('photo.jpg', 1200, 900),
        ])->assertRedirect();

        $this->assertDatabaseCount('media', 1);
        $meta = \App\Models\Core\Media::query()->firstOrFail()->meta;
        $this->assertArrayHasKey('variants', $meta);
        $this->assertArrayHasKey('thumb', $meta['variants']);
    }

    public function test_menu_reorder_updates_sort_order(): void
    {
        $user = $this->userForRole('content_manager');
        $menu = Menu::query()->create(['slug' => 'header', 'name' => 'Header']);
        $itemA = MenuItem::query()->create(['menu_id' => $menu->id, 'label' => 'A', 'url' => '/a', 'sort_order' => 0, 'is_active' => true]);
        $itemB = MenuItem::query()->create(['menu_id' => $menu->id, 'label' => 'B', 'url' => '/b', 'sort_order' => 1, 'is_active' => true]);

        $this->actingAs($user)->post(route('admin.menus.reorder', $menu), [
            'items' => [
                ['id' => $itemA->id, 'sort_order' => 2, 'parent_id' => null],
                ['id' => $itemB->id, 'sort_order' => 0, 'parent_id' => null],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('menu_items', ['id' => $itemA->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('menu_items', ['id' => $itemB->id, 'sort_order' => 0]);
    }

    private function userForRole(string $roleSlug): User
    {
        $user = User::factory()->create(['role' => $roleSlug]);
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }
}
