<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'is_default', 'is_enabled'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_enabled' => 'boolean'];
    }
}
