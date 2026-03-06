<?php

namespace Tests\Feature;

use App\Models\CmsPages\Page;
use App\Models\CmsPages\PageTranslation;
use App\Models\Core\Media;
use App\Models\Core\SeoMeta;
use App\Models\Core\Setting;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyImage;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\PostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase12CSeoAdvancedTest extends TestCase
{
    use RefreshDatabase;

    public function test_breadcrumbs_and_schema_are_present_on_property_and_blog_pages(): void
    {
        $property = Property::factory()->create(['slug' => 'riad-test']);

        $propertyResponse = $this->get('/properties/riad-test')->assertOk();
        $propertyResponse->assertSee('aria-label="Breadcrumb"', false);
        $propertyResponse->assertSee('RealEstateListing', false);

        $post = Post::query()->create(['slug' => 'seo-post', 'status' => 'published', 'published_at' => now()]);
        PostTranslation::query()->create(['post_id' => $post->id, 'locale' => 'en', 'title' => 'SEO Post', 'content' => 'Body']);

        $blogResponse = $this->get('/blog/seo-post')->assertOk();
        $blogResponse->assertSee('BreadcrumbList', false);
        $blogResponse->assertSee('Article', false);
    }

    public function test_schema_json_ld_is_valid_json(): void
    {
        $homePage = Page::query()->create(['slug' => 'home', 'template' => 'home', 'status' => 'published', 'published_at' => now()]);
        PageTranslation::query()->create(['page_id' => $homePage->id, 'locale' => 'en', 'title' => 'Home']);

        $response = $this->get('/')->assertOk();
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $response->getContent(), $matches);

        $this->assertNotEmpty($matches[1] ?? null);
        $decoded = json_decode($matches[1], true);
        $this->assertIsArray($decoded);
    }

    public function test_og_image_fallback_uses_default_setting_and_entity_cover(): void
    {
        $defaultMedia = Media::query()->create(['disk' => 'public', 'path' => 'seo/default.jpg', 'filename' => 'default.jpg']);
        Setting::query()->create(['group' => 'seo', 'key' => 'default_og_image_media_id', 'value' => $defaultMedia->id, 'type' => 'int']);

        $home = Page::query()->create(['slug' => 'home', 'template' => 'home', 'status' => 'published', 'published_at' => now()]);
        PageTranslation::query()->create(['page_id' => $home->id, 'locale' => 'en', 'title' => 'Homepage']);

        $this->get('/')->assertOk()->assertSee('og:image', false)->assertSee('seo/default.jpg', false);

        $property = Property::factory()->create(['slug' => 'cover-test']);
        $coverMedia = Media::query()->create(['disk' => 'public', 'path' => 'properties/cover.jpg', 'filename' => 'cover.jpg']);
        PropertyImage::query()->create(['property_id' => $property->id, 'media_id' => $coverMedia->id, 'is_cover' => true, 'sort_order' => 1]);

        SeoMeta::query()->create([
            'metaable_type' => Property::class,
            'metaable_id' => $property->id,
            'meta_title' => 'Property with cover',
            'locale' => 'en',
        ]);

        $this->get('/properties/cover-test')->assertOk()->assertSee('properties/cover.jpg', false);
    }

    public function test_sitemap_includes_image_tags_for_property_pages(): void
    {
        $property = Property::factory()->create(['slug' => 'sitemap-property']);
        $media = Media::query()->create(['disk' => 'public', 'path' => 'properties/sitemap.jpg', 'filename' => 'sitemap.jpg']);
        PropertyImage::query()->create(['property_id' => $property->id, 'media_id' => $media->id, 'is_cover' => true, 'sort_order' => 1]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<image:image>', false)
            ->assertSee('properties/sitemap.jpg', false);
    }

    public function test_hreflang_excludes_missing_locales_on_translated_entities(): void
    {
        $property = Property::factory()->create(['slug' => 'hreflang-property']);

        $response = $this->get('/properties/hreflang-property')->assertOk();
        $response->assertSee('hreflang="en"', false);
        $response->assertDontSee('hreflang="fr"', false);
    }

    public function test_non_regression_sitemap_and_canonical_still_available(): void
    {
        $property = Property::factory()->create(['slug' => 'canonical-property']);

        $this->get('/sitemap.xml')->assertOk();
        $this->get('/properties/'.$property->slug)
            ->assertOk()
            ->assertSee('<link rel="canonical"', false);
    }
}
