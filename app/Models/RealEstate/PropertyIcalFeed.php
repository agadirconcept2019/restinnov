<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Model;

class PropertyIcalFeed extends Model
{
    protected $fillable = [
        'property_id', 'feed_url', 'is_active', 'sync_interval_minutes', 'last_synced_at', 'last_status', 'last_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
