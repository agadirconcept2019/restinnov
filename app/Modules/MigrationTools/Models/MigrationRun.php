<?php

namespace App\Modules\MigrationTools\Models;

use Illuminate\Database\Eloquent\Model;

class MigrationRun extends Model
{
    protected $fillable = ['source_type', 'status', 'options', 'started_at', 'finished_at', 'summary'];

    protected $casts = [
        'options' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(MigrationItem::class, 'run_id');
    }
}
