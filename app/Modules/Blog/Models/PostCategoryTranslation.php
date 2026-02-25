<?php

namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategoryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['category_id', 'locale', 'name'];
}
