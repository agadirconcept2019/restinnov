<?php

namespace App\Models\Forms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_type', 'locale', 'payload', 'status', 'ip_hash', 'user_agent', 'source_url', 'submitted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'submitted_at' => 'integer',
    ];
}
