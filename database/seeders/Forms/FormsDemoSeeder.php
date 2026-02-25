<?php

namespace Database\Seeders\Forms;

use App\Models\Forms\FormSubmission;
use Illuminate\Database\Seeder;

class FormsDemoSeeder extends Seeder
{
    public function run(): void
    {
        FormSubmission::query()->create([
            'form_type' => 'contact',
            'locale' => 'en',
            'status' => 'new',
            'payload' => [
                'first_name' => 'Sarah',
                'last_name' => 'Owner',
                'email' => 'sarah.owner@example.com',
                'subject_type' => 'owner',
                'message' => 'I need management services for my apartment.',
            ],
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'source_url' => '/contact-us',
        ]);

        FormSubmission::query()->create([
            'form_type' => 'quote',
            'locale' => 'fr',
            'status' => 'new',
            'payload' => [
                'full_name' => 'Yassine Client',
                'email' => 'yassine.client@example.com',
                'message' => 'Je souhaite un devis pour une villa.',
            ],
            'ip_hash' => hash('sha256', '127.0.0.2'),
            'source_url' => '/contact-us',
        ]);
    }
}
