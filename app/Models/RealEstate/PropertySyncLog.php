<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Model;

class PropertySyncLog extends Model
{
    protected $fillable = [
        'property_ical_feed_id', 'started_at', 'ended_at', 'status', 'events_count', 'error_excerpt',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function feed()
    {
        return $this->belongsTo(PropertyIcalFeed::class, 'property_ical_feed_id');
    }
}
