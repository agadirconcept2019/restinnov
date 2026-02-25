<?php

namespace App\Modules\MigrationTools\Models;

use Illuminate\Database\Eloquent\Model;

class MigrationItem extends Model
{
    protected $fillable = [
        'run_id', 'entity_type', 'source_id', 'source_slug', 'target_type', 'target_id',
        'status', 'error_excerpt', 'payload_hash',
    ];

    public function run()
    {
        return $this->belongsTo(MigrationRun::class, 'run_id');
    }
}
