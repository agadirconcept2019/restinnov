<?php

namespace App\Models\Core;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportRun extends Model
{
    protected $fillable = ['user_id', 'resource_key', 'status', 'disk', 'file_path', 'rows_count', 'error_excerpt', 'completed_at'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
