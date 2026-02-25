<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk','path','filename','original_name','mime_type','size','width','height','alt','title','caption','uploaded_by','meta'
    ];

    protected $casts = ['meta' => 'array'];
}
