<?php

namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    protected $fillable = ['slug', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];

    public function translations()
    {
        return $this->hasMany(PostCategoryTranslation::class, 'category_id');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_category_post', 'post_category_id', 'post_id');
    }

    public function translated(?string $locale = null): ?PostCategoryTranslation
    {
        $locale ??= app()->getLocale();
        $fallback = config('locales.default', 'en');

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', $fallback)
            ?? $this->translations->first();
    }
}
