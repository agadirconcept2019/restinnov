<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    protected $fillable = [
        'property_id', 'checkin_date', 'checkout_date', 'guests', 'full_name', 'email', 'phone',
        'message', 'status', 'estimated_total', 'locale', 'ip_hash', 'user_agent', 'source_url',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'estimated_total' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
