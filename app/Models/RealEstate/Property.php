<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\PropertyFactory::new();
    }

    protected $fillable = [
        'slug', 'title', 'status', 'city_id', 'base_price_per_night', 'currency',
        'max_guests', 'bedrooms', 'bathrooms', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }
}
