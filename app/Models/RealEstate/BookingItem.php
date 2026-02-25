<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = ['booking_id', 'date', 'price_per_night', 'line_total'];
    protected $casts = ['date' => 'date', 'price_per_night' => 'decimal:2', 'line_total' => 'decimal:2'];

    public function booking() { return $this->belongsTo(Booking::class); }
}
