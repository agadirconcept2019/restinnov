<?php

namespace App\Modules\Communications\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'template_key', 'locale', 'to_email_masked', 'to_email_encrypted', 'payload_encrypted', 'subject', 'status', 'attempts',
        'last_error_excerpt', 'related_type', 'related_id', 'queued_at', 'sent_at',
        'failed_at', 'meta',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'meta' => 'array',
    ];
}
