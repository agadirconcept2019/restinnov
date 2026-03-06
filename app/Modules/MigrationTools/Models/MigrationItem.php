<?php

namespace App\Modules\MigrationTools\Models;

use Illuminate\Database\Eloquent\Model;

class MigrationItem extends Model
{
    protected $fillable = [
        'run_id', 'entity_type', 'source_id', 'source_slug', 'target_type', 'target_id',
        'status', 'result', 'error_excerpt', 'payload_hash', 'rolled_back_at',
    ];

    protected $casts = [
        'rolled_back_at' => 'datetime',
    ];

    public function run()
    {
        return $this->belongsTo(MigrationRun::class, 'run_id');
    }
}
