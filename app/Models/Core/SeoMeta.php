<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'metaable_type', 'metaable_id', 'locale',
        'meta_title', 'meta_description', 'canonical_url',
        'og_title', 'og_description', 'og_image_media_id', 'robots', 'schema_json',
    ];

    protected $casts = [
        'schema_json' => 'array',
    ];

    public function metaable()
    {
        return $this->morphTo();
    }
}
