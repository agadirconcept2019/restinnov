<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'version',
        'is_enabled',
        'installed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'installed_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
