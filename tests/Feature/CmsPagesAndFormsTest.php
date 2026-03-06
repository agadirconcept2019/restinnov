<?php

namespace Tests\Feature;

use App\Models\Forms\FormSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CmsPagesAndFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_pages_return_200(): void
    {
        $this->seed();

        $this->get('/our-services')->assertOk();
        $this->get('/rd')->assertOk();
        $this->get('/faq')->assertOk();
        $this->get('/contact-us')->assertOk();
        $this->get('/terms-and-conditions')->assertOk();
        $this->get('/fr/our-services')->assertOk();
    }

    public function test_contact_form_validates_and_stores_submission(): void
    {
        $this->seed();

        $this->post('/forms/contact', [])->assertSessionHasErrors(['first_name', 'last_name', 'email', 'subject_type', 'message']);

        $this->post('/forms/contact', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'subject_type' => 'owner',
            'message' => 'Hello',
            'company_name' => '',
            'submitted_at' => time() - 5,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('form_submissions', ['form_type' => 'contact', 'status' => 'new']);
    }

    public function test_quote_form_validates_and_stores_submission(): void
    {
        $this->seed();

        $this->post('/forms/quote', [
            'full_name' => 'Quote User',
            'email' => 'quote@example.com',
            'message' => 'Need quote',
            'company_name' => '',
            'submitted_at' => time() - 5,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('form_submissions', ['form_type' => 'quote']);
    }

    public function test_honeypot_rejects_spam(): void
    {
        $this->seed();

        $this->post('/forms/contact', [
            'first_name' => 'Spam',
            'last_name' => 'Bot',
            'email' => 'spam@example.com',
            'subject_type' => 'other',
            'message' => 'spam',
            'company_name' => 'bot-filled',
            'submitted_at' => time() - 5,
        ])->assertSessionHasErrors(['company_name']);
    }

    public function test_install_lock_still_blocks_install_non_regression(): void
    {
        File::put(storage_path('app/install.lock'), 'locked');

        $this->get('/install')->assertRedirect(route('admin.login'));

        File::delete(storage_path('app/install.lock'));
    }

    public function test_realestate_routes_still_work_non_regression(): void
    {
        $this->seed();

        $this->get('/our-properties')->assertOk();
    }
}
