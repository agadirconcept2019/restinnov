<?php

namespace Tests\Feature;

use App\Models\Core\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase5SeoPerfSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_head_present_on_key_pages_and_canonical_absolute(): void
    {
        $this->seed();

        foreach (['/', '/our-properties', '/blog', '/our-services'] as $path) {
            $response = $this->get($path)->assertOk();
            $response->assertSee('<link rel="canonical" href="http', false);
            $response->assertSee('rel="alternate" hreflang="en"', false);
            // Hreflang alternates are now emitted only for locales with existing translations.
        }
    }

    public function test_sitemap_contains_expected_urls(): void
    {
        $this->seed();

        $response = $this->get('/sitemap.xml')->assertOk();
        $response->assertSee(url('/'));
        $response->assertSee('/properties/');
        $response->assertSee('/blog/');
        $response->assertSee('/our-services');
    }

    public function test_redirects_apply_and_hits_increment(): void
    {
        $this->seed();

        $redirect = Redirect::query()->create([
            'from_path' => '/legacy-page',
            'to_url' => '/our-services',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/legacy-page?x=1')->assertRedirect('/our-services?x=1');
        $this->assertDatabaseHas('redirects', ['id' => $redirect->id, 'hits' => 1]);
    }

    public function test_submission_stores_even_if_mail_dispatch_fails(): void
    {
        $this->seed();

        Bus::shouldReceive('dispatch')->andThrow(new \RuntimeException('queue down'));

        $this->post('/forms/contact', [
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 'ab@example.com',
            'subject_type' => 'owner',
            'message' => 'Hello',
            'company_name' => '',
            'submitted_at' => time() - 5,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('form_submissions', ['form_type' => 'contact']);
    }

    public function test_non_regressions_install_lock_and_public_routes(): void
    {
        $this->seed();

        File::put(storage_path('app/install.lock'), 'locked');
        $this->get('/install')->assertRedirect(route('admin.login'));
        File::delete(storage_path('app/install.lock'));

        $this->get('/our-properties')->assertOk();
        $this->get('/blog')->assertOk();
    }
}
