<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallState extends Model
{
    use HasFactory;

    protected $table = 'install_state';

    protected $fillable = [
        'current_step',
        'payload',
        'is_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }
}
