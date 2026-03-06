<?php

namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'post_id', 'locale', 'title', 'excerpt', 'content', 'meta_title', 'meta_description', 'canonical_url',
    ];
}
