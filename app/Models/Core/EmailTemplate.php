<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['key', 'locale', 'subject', 'body_html', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
