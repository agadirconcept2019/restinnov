<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_user_id', 'action', 'entity_type', 'entity_id', 'meta', 'ip_hash', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];
}
