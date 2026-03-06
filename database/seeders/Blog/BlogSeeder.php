<?php

namespace Database\Seeders\Blog;

use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\PostCategory;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];
        foreach ([
            'news' => ['en' => 'News', 'fr' => 'Actualités'],
            'tips' => ['en' => 'Tips', 'fr' => 'Conseils'],
            'guide' => ['en' => 'Guide', 'fr' => 'Guide'],
        ] as $slug => $labels) {
            $cat = PostCategory::query()->updateOrCreate(['slug' => $slug], ['is_active' => true, 'sort_order' => count($categories)]);
            $cat->translations()->updateOrCreate(['locale' => 'en'], ['name' => $labels['en']]);
            $cat->translations()->updateOrCreate(['locale' => 'fr'], ['name' => $labels['fr']]);
            $categories[$slug] = $cat;
        }

        $posts = [
            ['slug' => 'restinnov-launches-quality-framework', 'category' => 'news', 'title' => 'RestInnov launches quality framework'],
            ['slug' => '5-ways-to-improve-owner-yields', 'category' => 'tips', 'title' => '5 ways to improve owner yields'],
            ['slug' => 'short-term-rental-ops-guide', 'category' => 'guide', 'title' => 'Short-term rental operations guide'],
            ['slug' => 'guest-experience-playbook', 'category' => 'tips', 'title' => 'Guest experience playbook'],
        ];

        foreach ($posts as $row) {
            $post = Post::query()->updateOrCreate(
                ['slug' => $row['slug']],
                ['status' => 'published', 'published_at' => now()->subDays(rand(1, 60)), 'is_featured' => false],
            );

            $post->translations()->updateOrCreate(['locale' => 'en'], [
                'title' => $row['title'],
                'excerpt' => 'Operational insight for property owners and travelers.',
                'content' => 'This article explains practical processes to improve quality, occupancy and guest experience in a managed rental operation.',
                'meta_title' => $row['title'].' | Blog',
                'meta_description' => 'Professional insight from RestInnov blog.',
            ]);
            $post->translations()->updateOrCreate(['locale' => 'fr'], [
                'title' => 'FR - '.$row['title'],
                'excerpt' => 'Conseils opérationnels pour propriétaires et voyageurs.',
                'content' => 'Cet article présente des pratiques concrètes pour améliorer la qualité, le taux d’occupation et l’expérience client.',
                'meta_title' => 'FR - '.$row['title'].' | Blog',
                'meta_description' => 'Conseils professionnels du blog RestInnov.',
            ]);

            $post->categories()->sync([$categories[$row['category']]->id]);
        }
    }
}
