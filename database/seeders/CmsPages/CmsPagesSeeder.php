<?php

namespace Database\Seeders\CmsPages;

use App\Models\CmsPages\Page;
use Illuminate\Database\Seeder;

class CmsPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['slug' => 'home', 'template' => 'home'],
            ['slug' => 'our-services', 'template' => 'services'],
            ['slug' => 'rd', 'template' => 'rd'],
            ['slug' => 'faq', 'template' => 'faq'],
            ['slug' => 'contact-us', 'template' => 'contact'],
            ['slug' => 'terms-and-conditions', 'template' => 'legal'],
        ];

        foreach ($pages as $p) {
            $page = Page::query()->updateOrCreate(
                ['slug' => $p['slug']],
                ['template' => $p['template'], 'status' => 'published', 'published_at' => now()],
            );

            $page->translations()->updateOrCreate(['locale' => 'en'], [
                'title' => match ($p['slug']) {
                    'home' => 'Professional Property Management in Morocco',
                    'our-services' => 'Our Services',
                    'rd' => 'R&D and Innovation',
                    'faq' => 'Frequently Asked Questions',
                    'contact-us' => 'Contact Us',
                    default => 'Terms and Conditions',
                },
                'content' => 'Professional content for '.$p['slug'].' page. This content is editable in CMS.',
                'template_data' => [
                    'hero' => ['title' => 'RestInnov', 'subtitle' => 'Hospitality operations with quality standards', 'cta_label' => 'Request a quote', 'cta_url' => '/contact-us'],
                    'featured_properties' => ['enabled' => true, 'count' => 6],
                ],
                'meta_title' => ucfirst(str_replace('-', ' ', $p['slug'])).' | RestInnov',
                'meta_description' => 'RestInnov '.str_replace('-', ' ', $p['slug']).' page.',
            ]);

            $page->translations()->updateOrCreate(['locale' => 'fr'], [
                'title' => match ($p['slug']) {
                    'home' => 'Gestion immobilière professionnelle au Maroc',
                    'our-services' => 'Nos Services',
                    'rd' => 'R&D et Innovation',
                    'faq' => 'FAQ',
                    'contact-us' => 'Contact',
                    default => 'Conditions Générales',
                },
                'content' => 'Contenu professionnel pour la page '.$p['slug'].'.',
                'template_data' => [
                    'hero' => ['title' => 'RestInnov', 'subtitle' => 'Excellence opérationnelle', 'cta_label' => 'Demander un devis', 'cta_url' => '/contact-us'],
                    'featured_properties' => ['enabled' => true, 'count' => 6],
                ],
                'meta_title' => ucfirst(str_replace('-', ' ', $p['slug'])).' | RestInnov',
                'meta_description' => 'Page '.str_replace('-', ' ', $p['slug']).' RestInnov.',
            ]);
        }
    }
}
