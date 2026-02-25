<?php

namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['slug', 'status', 'published_at', 'author_id', 'is_featured'];
    protected $casts = ['published_at' => 'datetime', 'is_featured' => 'boolean'];

    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function categories()
    {
        return $this->belongsToMany(PostCategory::class, 'post_category_post', 'post_id', 'post_category_id');
    }

    public function translated(?string $locale = null): ?PostTranslation
    {
        $locale ??= app()->getLocale();
        $fallback = config('locales.default', 'en');

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', $fallback)
            ?? $this->translations->first();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }
}
