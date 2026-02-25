<?php

namespace Tests\Feature;

use App\Models\RealEstate\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_validates_and_stores_submission(): void
    {
        $response = $this->post('/contact-us', []);
        $response->assertSessionHasErrors(['name', 'email', 'message']);

        $this->post('/contact-us', [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'message' => 'Hello',
            'website' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('form_submissions', 1);
    }

    public function test_property_inquiry_validates_and_stores_submission(): void
    {
        $property = Property::factory()->create();

        $this->post('/properties/'.$property->id.'/inquiry', [
            'name' => 'John',
            'email' => 'john@example.com',
            'message' => 'Need availability',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('property_inquiries', 1);
    }
}
