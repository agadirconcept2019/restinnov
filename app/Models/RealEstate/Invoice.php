<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'booking_id', 'invoice_number', 'issued_at', 'due_at', 'billing_name', 'billing_address',
        'vat_id', 'currency', 'subtotal', 'taxes_total', 'total', 'status', 'pdf_path', 'meta',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'meta' => 'array',
        'subtotal' => 'decimal:2',
        'taxes_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function booking() { return $this->belongsTo(Booking::class); }
}
