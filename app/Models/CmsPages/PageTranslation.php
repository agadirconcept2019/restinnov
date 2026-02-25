<?php

namespace App\Models\CmsPages;

use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    protected $fillable = [
        'page_id', 'locale', 'title', 'excerpt', 'content', 'template_data',
        'meta_title', 'meta_description', 'canonical_url',
    ];

    protected $casts = [
        'template_data' => 'array',
    ];
}
