<?php

namespace Tests\Feature;

use App\Jobs\RunAdminExportJob;
use App\Models\CmsPages\Page;
use App\Models\Core\Role;
use App\Models\Core\UserSavedView;
use App\Models\RealEstate\Booking;
use App\Models\RealEstate\BookingRequest;
use App\Models\RealEstate\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class Phase12AAdminProductivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_and_export_are_rbac_protected(): void
    {
        Bus::fake();

        $support = $this->userForRole('support');
        $property = Property::factory()->create();

        $this->actingAs($support)
            ->post(route('admin.real-estate.properties.bulk'), ['ids' => [$property->id], 'action' => 'publish'])
            ->assertForbidden();

        $editor = $this->userForRole('editor');
        $this->actingAs($editor)
            ->post(route('admin.exports.queue'), ['resource_key' => 'properties'])
            ->assertForbidden();

        $contentManager = $this->userForRole('content_manager');
        $this->actingAs($contentManager)
            ->post(route('admin.exports.queue'), ['resource_key' => 'properties'])
            ->assertRedirect();

        Bus::assertDispatched(RunAdminExportJob::class);
    }

    public function test_saved_views_crud_for_properties(): void
    {
        $manager = $this->userForRole('admin');

        $this->actingAs($manager)
            ->from('/admin/real-estate/properties?status=published&featured=1')
            ->post('/admin/saved-views?status=published&featured=1', [
                'resource_key' => 'properties',
                'name' => 'Featured published',
                'is_default' => 1,
            ])
            ->assertRedirect();

        $view = UserSavedView::query()->where('user_id', $manager->id)->where('resource_key', 'properties')->firstOrFail();
        $this->assertTrue($view->is_default);
        $this->assertSame('published', $view->query_json['status']);

        $this->actingAs($manager)
            ->post(route('admin.saved-views.apply', $view))
            ->assertRedirect();

        $this->actingAs($manager)
            ->delete(route('admin.saved-views.destroy', $view))
            ->assertRedirect();

        $this->assertDatabaseMissing('user_saved_views', ['id' => $view->id]);
    }

    public function test_bulk_actions_update_properties_bookings_requests_and_pages(): void
    {
        $manager = $this->userForRole('admin');

        $property = Property::factory()->create(['status' => 'draft']);
        $booking = Booking::query()->create([
            'property_id' => $property->id,
            'guest_full_name' => 'Guest Example',
            'guest_email' => 'g@example.test',
            'guest_phone' => '+212600000000',
            'status' => 'pending',
            'currency' => 'MAD',
            'subtotal' => 100,
            'taxes_total' => 0,
            'total' => 100,
            'checkin_date' => now()->toDateString(),
            'checkout_date' => now()->addDay()->toDateString(),
            'nights' => 1,
            'guests' => 2,
            'source' => 'admin',
        ]);
        $request = BookingRequest::query()->create([
            'property_id' => $property->id,
            'full_name' => 'Guest Request',
            'email' => 'r@example.test',
            'phone' => '+212600000001',
            'checkin_date' => now()->addDays(3)->toDateString(),
            'checkout_date' => now()->addDays(5)->toDateString(),
            'nights' => 2,
            'guests' => 2,
            'status' => 'pending',
            'estimated_total' => 200,
            'ip_hash' => 'x',
        ]);

        $page = Page::query()->create(['slug' => 'phase12-page', 'template' => 'services', 'status' => 'draft']);

        $this->actingAs($manager)->post(route('admin.real-estate.properties.bulk'), ['ids' => [$property->id], 'action' => 'publish'])->assertRedirect();
        $this->actingAs($manager)->post(route('admin.real-estate.bookings.bulk'), ['ids' => [$booking->id], 'status' => 'confirmed'])->assertRedirect();
        $this->actingAs($manager)->post(route('admin.real-estate.booking-requests.bulk'), ['ids' => [$request->id], 'status' => 'rejected'])->assertRedirect();
        $this->actingAs($manager)->post(route('admin.cms-pages.pages.bulk'), ['ids' => [$page->id], 'action' => 'publish'])->assertRedirect();

        $this->assertDatabaseHas('properties', ['id' => $property->id, 'status' => 'published']);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('booking_requests', ['id' => $request->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'status' => 'published']);
    }

    public function test_non_regression_public_install_and_owner_routes(): void
    {
        $this->get('/our-properties')->assertOk();
        $this->get('/blog')->assertOk();

        file_put_contents(storage_path('app/install.lock'), '1');
        $this->get('/install')->assertRedirect(route('admin.login'));

        $owner = User::factory()->create(['role' => 'owner']);
        $ownerRole = Role::query()->where('slug', 'owner')->firstOrFail();
        $owner->roles()->syncWithoutDetaching([$ownerRole->id]);

        $this->actingAs($owner)->get('/owner')->assertOk();
    }

    private function userForRole(string $roleSlug): User
    {
        $user = User::factory()->create(['role' => $roleSlug]);
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }
}
