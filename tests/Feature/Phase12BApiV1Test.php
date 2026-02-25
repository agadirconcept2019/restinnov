<?php

namespace Tests\Feature;

use App\Models\CmsPages\Page;
use App\Models\CmsPages\PageTranslation;
use App\Models\Forms\FormSubmission;
use App\Models\Core\Role;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyAvailability;
use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\PostTranslation;
use App\Modules\Communications\Models\EmailLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class Phase12BApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_auth_token_issue_and_logout(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-123'), 'role' => 'admin']);

        $issue = $this->postJson('/api/auth/token', [
            'email' => $user->email,
            'password' => 'secret-123',
            'device_name' => 'phpunit',
        ])->assertOk();

        $token = $issue->json('token');
        $this->assertNotEmpty($token);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout')
            ->assertOk();
    }

    public function test_public_endpoints_and_pagination_and_availability_filter(): void
    {
        $available = Property::factory()->create(['status' => 'published', 'published_at' => now(), 'max_guests' => 4]);
        $blocked = Property::factory()->create(['status' => 'published', 'published_at' => now(), 'max_guests' => 4]);

        PropertyAvailability::query()->create([
            'property_id' => $blocked->id,
            'date' => now()->addDays(2)->toDateString(),
            'status' => 'booked',
        ]);

        $post = Post::query()->create(['slug' => 'api-post', 'status' => 'published', 'published_at' => now()]);
        PostTranslation::query()->create(['post_id' => $post->id, 'locale' => 'en', 'title' => 'API Post']);

        $page = Page::query()->create(['slug' => 'api-page', 'template' => 'services', 'status' => 'published', 'published_at' => now()]);
        PageTranslation::query()->create(['page_id' => $page->id, 'locale' => 'en', 'title' => 'API Page']);

        $this->getJson('/api/v1/properties?per_page=1')->assertOk()->assertJsonPath('meta.per_page', 1);

        $this->getJson('/api/v1/properties?checkin_date='.now()->addDays(2)->toDateString().'&checkout_date='.now()->addDays(3)->toDateString())
            ->assertOk()
            ->assertJsonFragment(['slug' => $available->slug])
            ->assertJsonMissing(['slug' => $blocked->slug]);

        $this->getJson('/api/v1/properties/'.$available->slug)->assertOk()->assertJsonPath('data.slug', $available->slug);
        $this->getJson('/api/v1/blog/posts')->assertOk()->assertJsonFragment(['slug' => 'api-post']);
        $this->getJson('/api/v1/blog/posts/api-post')->assertOk()->assertJsonPath('data.slug', 'api-post');
        $this->getJson('/api/v1/pages/api-page')->assertOk()->assertJsonPath('data.slug', 'api-page');
    }

    public function test_rbac_denies_forbidden_private_endpoints(): void
    {
        $owner = $this->makeUserWithRole('owner');
        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/admin/properties')->assertForbidden();

        $editor = $this->makeUserWithRole('editor');
        Sanctum::actingAs($editor);
        $this->getJson('/api/v1/support/forms/submissions')->assertForbidden();
    }

    public function test_private_endpoints_by_role_and_no_pii_leaks(): void
    {
        $support = $this->makeUserWithRole('support');
        FormSubmission::query()->create(['form_type' => 'contact', 'payload' => ['email' => 'private@example.com'], 'status' => 'new', 'locale' => 'en', 'ip_hash' => 'hash', 'user_agent' => 'ua']);
        EmailLog::query()->create(['template_key' => 'booking.confirmed', 'to_email_masked' => 'j***@example.com', 'subject' => 'x', 'status' => 'failed', 'attempts' => 1]);

        Sanctum::actingAs($support);
        $this->getJson('/api/v1/support/forms/submissions')->assertOk()->assertJsonMissingPath('data.0.ip_hash')->assertJsonMissingPath('data.0.user_agent');
        $this->getJson('/api/v1/support/communications/email-logs')->assertOk()->assertJsonMissingPath('data.0.to_email_encrypted');

        $owner = $this->makeUserWithRole('owner');
        $property = Property::factory()->create(['owner_user_id' => $owner->id, 'status' => 'published', 'published_at' => now()]);
        $booking = Booking::query()->create([
            'property_id' => $property->id,
            'guest_full_name' => 'Owner Guest',
            'guest_email' => 'ownerguest@example.test',
            'status' => 'pending',
            'currency' => 'MAD',
            'subtotal' => 100,
            'taxes_total' => 0,
            'total' => 100,
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
            'nights' => 1,
            'guests' => 2,
        ]);
        BookingRequest::query()->create([
            'property_id' => $property->id,
            'full_name' => 'Owner Inquiry',
            'email' => 'inq@example.test',
            'checkin_date' => now()->addDays(3)->toDateString(),
            'checkout_date' => now()->addDays(4)->toDateString(),
            'guests' => 2,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($owner);
        $this->getJson('/api/v1/owner/properties')->assertOk()->assertJsonFragment(['slug' => $property->slug]);
        $this->getJson('/api/v1/owner/bookings')->assertOk()->assertJsonFragment(['id' => $booking->id]);
        $this->patchJson('/api/v1/owner/bookings/'.$booking->id.'/status', ['status' => 'confirmed'])->assertOk()->assertJsonPath('data.status', 'confirmed');
    }


    private function makeUserWithRole(string $roleSlug): User
    {
        $user = User::factory()->create(['role' => $roleSlug]);
        $role = Role::query()->where('slug', $roleSlug)->first();
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return $user;
    }

    public function test_web_non_regressions_still_ok(): void
    {
        $this->get('/our-properties')->assertOk();
        $this->get('/blog')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
    }
}
