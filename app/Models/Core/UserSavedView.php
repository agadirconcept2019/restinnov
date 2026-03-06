<?php

namespace App\Models\Core;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSavedView extends Model
{
    protected $fillable = ['user_id', 'resource_key', 'name', 'query_json', 'is_default'];

    protected $casts = [
        'query_json' => 'array',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
