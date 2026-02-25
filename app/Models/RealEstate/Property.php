<?php

namespace App\Models\RealEstate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected static function newFactory(){ return \Database\Factories\PropertyFactory::new(); }

    protected $fillable = [
        'slug','status','property_type_id','rental_mode_id','city_id','area_id','base_price_per_night','currency','max_guests','bedrooms','beds','bathrooms','checkin_from','checkout_until','address_line','latitude','longitude','is_featured','published_at','created_by','updated_by'
    ];

    protected function casts(): array
    {
        return ['published_at'=>'datetime','is_featured'=>'boolean'];
    }

    public function type(){ return $this->belongsTo(PropertyType::class,'property_type_id'); }
    public function mode(){ return $this->belongsTo(RentalMode::class,'rental_mode_id'); }
    public function city(){ return $this->belongsTo(City::class); }
    public function area(){ return $this->belongsTo(Area::class); }
    public function translations(){ return $this->hasMany(PropertyTranslation::class); }
    public function images(){ return $this->hasMany(PropertyImage::class)->orderBy('sort_order'); }
    public function amenities(){ return $this->belongsToMany(Amenity::class,'property_amenity'); }
    public function availabilities(){ return $this->hasMany(PropertyAvailability::class); }
    public function inquiries(){ return $this->hasMany(PropertyInquiry::class); }
    public function bookingRequests(){ return $this->hasMany(BookingRequest::class); }
    public function icalFeeds(){ return $this->hasMany(PropertyIcalFeed::class); }

    public function translated(?string $locale = null): ?PropertyTranslation
    {
        $locale ??= app()->getLocale();
        $defaultLocale = config('locales.default', 'en');

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', $defaultLocale)
            ?? $this->translations->first();
    }

    public function scopePublished($query)
    {
        return $query->where('status','published')->whereNotNull('published_at');
    }
}
