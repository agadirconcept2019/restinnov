<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'property_id', 'booking_request_id', 'checkin_date', 'checkout_date', 'nights', 'guests',
        'guest_full_name', 'guest_email', 'guest_phone', 'status', 'currency', 'subtotal',
        'taxes_total', 'fees_total', 'discount_total', 'total', 'locale', 'ip_hash', 'user_agent',
        'source_url', 'confirmed_at', 'canceled_at',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'confirmed_at' => 'datetime',
        'canceled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'taxes_total' => 'decimal:2',
        'fees_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function property() { return $this->belongsTo(Property::class); }
    public function bookingRequest() { return $this->belongsTo(BookingRequest::class); }
    public function items() { return $this->hasMany(BookingItem::class); }
    public function invoice() { return $this->hasOne(Invoice::class); }
}
