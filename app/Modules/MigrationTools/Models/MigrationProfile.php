<?php

namespace App\Modules\MigrationTools\Models;

use Illuminate\Database\Eloquent\Model;

class MigrationProfile extends Model
{
    protected $fillable = [
        'name', 'source_type', 'base_url', 'endpoints', 'locale_mapping', 'field_mapping',
        'media_settings', 'overwrite_strategy', 'delta_strategy', 'last_successful_run_at',
    ];

    protected $casts = [
        'endpoints' => 'array',
        'locale_mapping' => 'array',
        'field_mapping' => 'array',
        'media_settings' => 'array',
        'last_successful_run_at' => 'datetime',
    ];

    public function runs()
    {
        return $this->hasMany(MigrationRun::class, 'profile_id');
    }
}
