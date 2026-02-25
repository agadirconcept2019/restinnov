<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'disk', 'path', 'filename', 'original_name', 'mime_type', 'size', 'width', 'height', 'alt', 'title', 'caption', 'uploaded_by', 'meta',
    ];

    protected $casts = ['meta' => 'array'];
}
