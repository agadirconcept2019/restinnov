<?php

namespace App\Models\CmsPages;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'template', 'status', 'published_at', 'author_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function translations()
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translated(?string $locale = null): ?PageTranslation
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
